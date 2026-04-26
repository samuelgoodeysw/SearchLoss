<?php

namespace Scandiweb\SearchLoss\Model;

use Magento\Framework\App\ResourceConnection;

class SearchLossDataProvider
{
    protected ResourceConnection $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    private function applyDateFilter($select, string $period)
    {
        if ($period === '7') {
            $select->where('updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
        }

        if ($period === '30') {
            $select->where('updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
        }

        return $select;
    }

    private function getTotalSearches(string $period = 'all'): int
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from('search_query', ['total' => 'SUM(popularity)']);

        $this->applyDateFilter($select, $period);

        return (int)$connection->fetchOne($select);
    }

    private function getFailedSearchCount(string $period = 'all'): int
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from('search_query', ['total' => 'SUM(popularity)'])
            ->where('num_results = 0');

        $this->applyDateFilter($select, $period);

        return (int)$connection->fetchOne($select);
    }

    public function getAverageOrderValue(): float
    {
        $connection = $this->resource->getConnection();

        $aov = $connection->fetchOne(
            $connection->select()
                ->from('sales_order', ['avg_order_value' => 'AVG(grand_total)'])
                ->where('state != ?', 'canceled')
        );

        return (float)$aov;
    }

    public function getConversionRate(string $period = 'all'): float
    {
        $connection = $this->resource->getConnection();

        $searches = $this->getTotalSearches($period);

        $orders = (float)$connection->fetchOne(
            $connection->select()
                ->from('sales_order', ['total' => 'COUNT(*)'])
                ->where('state != ?', 'canceled')
        );

        if ($searches <= 0 || $orders <= 0) {
            return 0.02;
        }

        return $orders / $searches;
    }

    private function getCatalogueSignals(string $term): array
    {
        $connection = $this->resource->getConnection();
        $cleanTerm = trim($term);
        $likeTerm = '%' . $cleanTerm . '%';

        $productEntityTable = $this->resource->getTableName('catalog_product_entity');
        $productVarcharTable = $this->resource->getTableName('catalog_product_entity_varchar');
        $categoryVarcharTable = $this->resource->getTableName('catalog_category_entity_varchar');
        $eavAttributeTable = $this->resource->getTableName('eav_attribute');

        $skuMatches = (int)$connection->fetchOne(
            $connection->select()
                ->from($productEntityTable, ['total' => 'COUNT(*)'])
                ->where('sku LIKE ?', $likeTerm)
        );

        $productNameAttributeId = (int)$connection->fetchOne(
            $connection->select()
                ->from($eavAttributeTable, ['attribute_id'])
                ->where('attribute_code = ?', 'name')
                ->where('entity_type_id = (
                    SELECT entity_type_id
                    FROM ' . $this->resource->getTableName('eav_entity_type') . '
                    WHERE entity_type_code = "catalog_product"
                    LIMIT 1
                )')
                ->limit(1)
        );

        $categoryNameAttributeId = (int)$connection->fetchOne(
            $connection->select()
                ->from($eavAttributeTable, ['attribute_id'])
                ->where('attribute_code = ?', 'name')
                ->where('entity_type_id = (
                    SELECT entity_type_id
                    FROM ' . $this->resource->getTableName('eav_entity_type') . '
                    WHERE entity_type_code = "catalog_category"
                    LIMIT 1
                )')
                ->limit(1)
        );

        $productNameMatches = 0;

        if ($productNameAttributeId > 0) {
            $productNameMatches = (int)$connection->fetchOne(
                $connection->select()
                    ->from($productVarcharTable, ['total' => 'COUNT(DISTINCT entity_id)'])
                    ->where('attribute_id = ?', $productNameAttributeId)
                    ->where('value LIKE ?', $likeTerm)
            );
        }

        $categoryNameMatches = 0;

        if ($categoryNameAttributeId > 0) {
            $categoryNameMatches = (int)$connection->fetchOne(
                $connection->select()
                    ->from($categoryVarcharTable, ['total' => 'COUNT(DISTINCT entity_id)'])
                    ->where('attribute_id = ?', $categoryNameAttributeId)
                    ->where('value LIKE ?', $likeTerm)
            );
        }

        return [
            'skuMatches' => $skuMatches,
            'productNameMatches' => $productNameMatches,
            'categoryNameMatches' => $categoryNameMatches,
            'hasSkuMatch' => $skuMatches > 0,
            'hasProductNameMatch' => $productNameMatches > 0,
            'hasCategoryNameMatch' => $categoryNameMatches > 0,
            'hasCatalogueMatch' => ($skuMatches + $productNameMatches + $categoryNameMatches) > 0,
        ];
    }

    private function getFixType(string $term): string
    {
        $normalized = strtolower(trim($term));
        $catalogueSignals = $this->getCatalogueSignals($term);

        if ($catalogueSignals['hasSkuMatch']) {
            return 'Product exists but is not showing';
        }

        if (preg_match('/[a-z]*\d+[a-z\d\-\.]*/i', $term)) {
            return 'SKU or part number is not matching';
        }

        if ($catalogueSignals['hasProductNameMatch'] || $catalogueSignals['hasCategoryNameMatch']) {
            return 'Product exists but is not showing';
        }

        if (preg_match('/hendrickson|dexter|al-ko|lippert|bpw|meritor|febi|saf/i', $normalized)) {
            return 'Brand or product terms are missing';
        }

        if (preg_match('/air\s*bag|airbag|air\s*spring|air\s*suspension/i', $normalized)) {
            return 'Customers use different wording';
        }

        if (preg_match('/nano\s+lea\.?f|lea\.f|sprng|suspention|bushng|galvani[sz]ed/i', $normalized)) {
            return 'Spelling or format variant';
        }

        if (preg_match('/\b(axle|spring|suspension|brake|hub|bushing|bolt|nut|seal|kit|shackle|equalizer)\b/i', $normalized)) {
            return 'Product or category may be missing';
        }

        if (str_word_count($normalized) >= 3) {
            return 'Fitment or use case is unclear';
        }

        if (str_word_count($normalized) <= 1) {
            return 'Search term is too broad or unclear';
        }

        return 'Results are weak or badly ranked';
    }

    private function getSuggestedFix(string $term, string $fixType): string
    {
        $cleanTerm = trim($term);

        switch ($fixType) {
            case 'Brand or product terms are missing':
                return sprintf(
                    'Check whether matching products have the right brand, manufacturer, product family, model, and product-type data for "%s". If products exist, add the missing terms to searchable attributes and improve product naming or copy where useful.',
                    $cleanTerm
                );

            case 'Customers use different wording':
                return sprintf(
                    'Check whether "%s" means the same thing as an existing product or category. If it does, add it as a synonym or searchable term, and update product/category copy only where the wording is accurate and natural.',
                    $cleanTerm
                );

            case 'SKU or part number is not matching':
                return sprintf(
                    'Check whether "%s" matches a SKU, manufacturer part number, alternate part number, old part number, replacement part, barcode, or common customer-used format. Prioritise exact and normalised matches before broad keyword results.',
                    $cleanTerm
                );

            case 'Product or category may be missing':
                return sprintf(
                    'Check whether the store sells "%s", an equivalent product, or a close substitute. If it exists, improve findability. If not, treat repeated searches as catalogue demand or route customers to the closest helpful alternative.',
                    $cleanTerm
                );

            case 'Spelling or format variant':
                return sprintf(
                    'Check whether "%s" is a common typo, abbreviation, spacing, punctuation, or singular/plural variant. Add it only when the intended product is clear, and avoid broad matches for SKU-like terms.',
                    $cleanTerm
                );

            case 'Fitment or use case is unclear':
                return sprintf(
                    'Check whether "%s" describes a specific application, compatibility need, model, size, material, system, or use case. If relevant products exist, add structured fitment data and clear product copy that connects the need to the right products.',
                    $cleanTerm
                );

            case 'Search term is too broad or unclear':
                return sprintf(
                    'Do not force a narrow synonym or redirect for "%s". Help customers narrow the search with better categories, filters, suggested terms, and result ordering.',
                    $cleanTerm
                );

            case 'Results are weak or badly ranked':
                return sprintf(
                    'Search "%s" manually and review the top results. If the right products exist but rank poorly, adjust searchable attributes, search weights, product data, ranking rules, or merchandising boosts.',
                    $cleanTerm
                );

            default:
                return sprintf(
                    'Check whether "%s" maps to a product, category, SKU, brand, synonym, redirect, compatibility need, or catalogue gap. If it repeats or has high revenue at risk, assign it to a clearer fix type after review.',
                    $cleanTerm
                );
        }
    }

    private function getPlainEnglishMeaning(string $term, string $fixType): string
    {
        $cleanTerm = trim($term);

        switch ($fixType) {
            case 'Product exists but is not showing':
                return sprintf(
                    'A matching product or category may already exist for "%s", but Magento search may not be showing it to customers.',
                    $cleanTerm
                );

            case 'Brand or product terms are missing':
                return sprintf(
                    '"%s" looks like a brand, manufacturer, model, or product-type search. Relevant products may exist, but they may not include the right searchable brand or product terms.',
                    $cleanTerm
                );

            case 'Customers use different wording':
                return sprintf(
                    'Customers may be using "%s" to describe something the catalogue calls by a different name.',
                    $cleanTerm
                );

            case 'SKU or part number is not matching':
                return sprintf(
                    '"%s" looks like a SKU, part number, manufacturer number, old part number, or customer-used identifier that search is not matching correctly.',
                    $cleanTerm
                );

            case 'Product or category may be missing':
                return sprintf(
                    'Customers may be searching for "%s", but the store may not clearly offer it, expose it, or route customers to the closest useful alternative.',
                    $cleanTerm
                );

            case 'Spelling or format variant':
                return sprintf(
                    '"%s" may be a typo, abbreviation, spacing variant, punctuation variant, or singular/plural version of a product customers expect to find.',
                    $cleanTerm
                );

            case 'Fitment or use case is unclear':
                return sprintf(
                    '"%s" may describe a specific fitment, application, size, model, material, system, or use case that product data does not clearly answer.',
                    $cleanTerm
                );

            case 'Search term is too broad or unclear':
                return sprintf(
                    '"%s" is broad or ambiguous, so customers may need better categories, filters, suggested terms, or result ordering to narrow their intent.',
                    $cleanTerm
                );

            case 'Results are weak or badly ranked':
                return sprintf(
                    'The right products for "%s" may exist, but they may be buried below weaker or irrelevant search results.',
                    $cleanTerm
                );

            default:
                return sprintf(
                    'Customers are telling you they want "%s", but Magento search may not be connecting that demand to a useful product, category, synonym, or search result.',
                    $cleanTerm
                );
        }
    }


    private function getMagentoFixSteps(string $term, string $fixType): array
    {
        $cleanTerm = trim($term);

        switch ($fixType) {
            case 'Brand or product terms are missing':
                return [
                    'Search Magento products for the term and close variations.',
                    'Review brand, manufacturer, model, product family, and product-type attributes.',
                    'Add missing terms to searchable product attributes where accurate.',
                    'Improve product names or descriptions where useful.',
                    'Reindex Magento search data and test the search again.',
                ];

            case 'Customers use different wording':
                return [
                    'Check whether the term maps to an existing product or category.',
                    'Identify the catalogue wording currently used for the same product.',
                    'Add safe synonyms or searchable terms where the meaning is the same.',
                    'Update product or category copy only where the wording is accurate.',
                    'Reindex Magento search data and test the search again.',
                ];

            case 'SKU or part number is not matching':
                return [
                    'Check whether the term matches a SKU, manufacturer part number, alternate part number, old part number, replacement part, or barcode.',
                    'Review whether those identifiers are stored on the correct product.',
                    'Make sure SKU and part-number fields are searchable where appropriate.',
                    'Add alternate identifiers to product data if they are valid.',
                    'Reindex Magento search data and test exact-match search behaviour.',
                ];

            case 'Product or category may be missing':
                return [
                    'Check whether the store already sells this product, an equivalent product, or a close substitute.',
                    'If products exist, improve product naming, searchable attributes, category assignment, and discoverability.',
                    'If a category exists, consider routing customers to the best category or landing page.',
                    'If the store does not sell it, treat repeated searches as catalogue demand.',
                    'Reindex Magento search data and test the search again.',
                ];

            case 'Spelling or format variant':
                return [
                    'Search for the intended product using corrected spelling or formatting.',
                    'Identify common typo, punctuation, spacing, abbreviation, or singular/plural variants.',
                    'Add safe variants to searchable product data or synonyms where the intent is clear.',
                    'Avoid broad matching when the term looks SKU-like.',
                    'Reindex Magento search data and test the search again.',
                ];

            case 'Fitment or use case is unclear':
                return [
                    'Review whether the term refers to compatibility, fitment, size, model, material, system, or application.',
                    'Check whether relevant products include structured fitment or use-case data.',
                    'Add compatibility, dimensions, material, or application data where useful.',
                    'Improve product copy so customers can connect the use case to the right product.',
                    'Reindex Magento search data and test the search again.',
                ];

            case 'Search term is too broad or unclear':
                return [
                    'Review the current search journey manually.',
                    'Avoid forcing a narrow synonym or redirect unless the intent is clear.',
                    'Improve categories, filters, and suggested search refinements.',
                    'Use merchandising or ranking rules to improve the most useful results.',
                    'Retest the storefront search experience.',
                ];

            case 'Results are weak or badly ranked':
                return [
                    'Search the term manually and review the top results.',
                    'Check whether the right products exist but are buried below weaker results.',
                    'Review searchable attributes and search weights.',
                    'Reduce noisy attributes if they pollute search results.',
                    'Reindex Magento search data and test again.',
                ];

            default:
                return [
                    'Search the term manually in Magento and on the storefront.',
                    'Review matching products, categories, synonyms, redirects, and searchable attributes.',
                    'Check whether this is a catalogue gap or a findability issue.',
                    'Assign a clearer fix type after review.',
                    'Reindex Magento search data and test again.',
                ];
        }
    }

    private function getOpportunityScore(int $count, float $lostRevenue): string
    {
        if ($count >= 10 || $lostRevenue >= 300) {
            return 'High';
        }

        if ($count >= 3 || $lostRevenue >= 100) {
            return 'Medium';
        }

        return 'Low';
    }

    private function getConfidence(int $count, string $fixType): string
    {
        if ($count >= 10) {
            return 'High';
        }

        if ($count >= 3) {
            return 'Medium';
        }

        if (in_array($fixType, ['Part number mapping', 'Brand/product tagging', 'Synonym mapping'], true)) {
            return 'Medium';
        }

        return 'Low';
    }

    public function getFailedSearchTerms(string $period = 'all'): array
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from('search_query', ['query_text', 'num_results', 'popularity', 'updated_at'])
            ->where('num_results = 0')
            ->order('popularity DESC')
            ->limit(20);

        $this->applyDateFilter($select, $period);

        $terms = $connection->fetchAll($select);

        $aov = $this->getAverageOrderValue();
        $conversionRate = $this->getConversionRate($period);

        foreach ($terms as &$term) {
            $term['lost_revenue'] = (float)$term['popularity'] * $aov * $conversionRate;
        }

        usort($terms, function ($a, $b) {
            return $b['lost_revenue'] <=> $a['lost_revenue'];
        });

        return $terms;
    }

    public function getWeakSearchTerms(string $period = 'all'): array
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from('search_query', ['query_text', 'num_results', 'popularity', 'updated_at'])
            ->where('num_results > 0')
            ->order('popularity DESC')
            ->limit(20);

        $this->applyDateFilter($select, $period);

        $terms = $connection->fetchAll($select);

        foreach ($terms as &$term) {
            $results = max((int)$term['num_results'], 1);
            $popularity = (int)$term['popularity'];

            $term['result_density'] = round($results / max($popularity, 1), 2);
            $term['opportunity_score'] = round($popularity / $results, 2);
        }

        usort($terms, function ($a, $b) {
            return $b['opportunity_score'] <=> $a['opportunity_score'];
        });

        return $terms;
    }

    public function getGa4FunnelTerms(): array
    {
        $connection = $this->resource->getConnection();

        return $connection->fetchAll(
            $connection->select()
                ->from('scandiweb_searchloss_ga4_term')
                ->order('searches DESC')
                ->limit(20)
        );
    }

    public function getOpportunityInsights(): array
    {
        $connection = $this->resource->getConnection();

        $rows = $connection->fetchAll(
            $connection->select()
                ->from('scandiweb_searchloss_ga4_term')
                ->order('searches DESC')
                ->limit(20)
        );

        $aov = $this->getAverageOrderValue();

        foreach ($rows as &$row) {
            $searches = (int)$row['searches'];
            $views = (int)$row['product_views'];
            $purchases = (int)$row['purchases'];

            $row['purchase_rate'] = $searches > 0 ? round(($purchases / $searches) * 100, 2) : 0;
            $row['missed_purchases'] = max($searches - $purchases, 0);
            $row['estimated_missed_revenue'] = $row['missed_purchases'] * $aov;

            if ($views === 0) {
                $row['issue'] = 'Zero Results';
            } elseif ($purchases === 0) {
                $row['issue'] = 'Weak Results';
            } elseif ($row['purchase_rate'] < 5) {
                $row['issue'] = 'Low Conversion';
            } else {
                $row['issue'] = 'OK';
            }
        }

        usort($rows, function ($a, $b) {
            return $b['estimated_missed_revenue'] <=> $a['estimated_missed_revenue'];
        });

        return $rows;
    }

    public function getSummary(string $period = 'all'): array
    {
        $failed = $this->getFailedSearchTerms($period);

        $totalFailedSearches = 0;
        $totalLostRevenue = 0;

        foreach ($failed as $term) {
            $totalFailedSearches += (int)$term['popularity'];
            $totalLostRevenue += (float)$term['lost_revenue'];
        }

        return [
            'failed_terms' => count($failed),
            'failed_searches' => $totalFailedSearches,
            'lost_revenue' => $totalLostRevenue,
            'aov' => $this->getAverageOrderValue(),
            'conversion_rate' => $this->getConversionRate($period) * 100,
        ];
    }

    public function getDashboardData(string $period = 'all'): array
    {
        $summary = $this->getSummary($period);
        $failedTerms = $this->getFailedSearchTerms($period);

        $totalSearches = $this->getTotalSearches($period);
        $failedSearches = $this->getFailedSearchCount($period);
        $zeroResultRate = $totalSearches > 0 ? ($failedSearches / $totalSearches) * 100 : 0;

        $externalTerms = [];

        foreach ($failedTerms as $term) {
            $termText = (string)$term['query_text'];
            $count = (int)$term['popularity'];
            $lostRevenue = round((float)$term['lost_revenue'], 2);
            $fixType = $this->getFixType($termText);

            $externalTerms[] = [
                'term' => $termText,
                'count' => $count,
                'conversion' => 0,
                'lostRevenue' => $lostRevenue,
                'opportunityScore' => $this->getOpportunityScore($count, $lostRevenue),
                'fixType' => $fixType,
                'suggestedFix' => $this->getSuggestedFix($termText, $fixType),
                'plainEnglishMeaning' => $this->getPlainEnglishMeaning($termText, $fixType),
                'magentoFixSteps' => $this->getMagentoFixSteps($termText, $fixType),
                'confidence' => $this->getConfidence($count, $fixType),
                'trend' => 'up'
            ];
        }

        return [
            [
                'key' => 'searchData',
                'value' => [
                    'totalSearches' => $totalSearches,
                    'failedSearches' => $failedSearches,
                    'zeroResultRate' => round($zeroResultRate, 2),
                    'searchToOrderRate' => round($summary['conversion_rate'], 2),
                    'averageOrderValue' => round($summary['aov'], 2),
                    'modeledDemandLost' => round($summary['lost_revenue'], 2)
                ]
            ],
            [
                'key' => 'failedSearchTerms',
                'value' => $externalTerms
            ]
        ];
    }
}