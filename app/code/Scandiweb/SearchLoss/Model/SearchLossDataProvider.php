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

    private function getFixType(string $term): string
    {
        $normalized = strtolower(trim($term));

        if (preg_match('/[a-z]*\d+[a-z\d\-\.]*/i', $term)) {
            return 'Part number mapping';
        }

        if (preg_match('/\b(axle|spring|suspension|brake|hub|bushing|bolt|nut|seal|kit)\b/i', $normalized)) {
            return 'Product/category coverage';
        }

        if (preg_match('/[\.]{1,}|[^\w\s\-]/', $term)) {
            return 'Spelling or formatting variant';
        }

        if (str_word_count($normalized) >= 3) {
            return 'Long-tail search intent';
        }

        return 'Search relevance';
    }

    private function getSuggestedFix(string $term, string $fixType): string
    {
        switch ($fixType) {
            case 'Part number mapping':
                return 'Map this part number, brand term, or SKU-like query to matching products. Add redirects or product tags if needed.';

            case 'Product/category coverage':
                return 'Check whether this product/category exists. If it does, improve tags, synonyms, and search redirects. If not, consider adding catalogue coverage.';

            case 'Spelling or formatting variant':
                return 'Add spelling, punctuation, or formatting variants as synonyms so customers still reach the right products.';

            case 'Long-tail search intent':
                return 'Create or improve a targeted landing page, category page, or search redirect for this specific buying intent.';

            default:
                return 'Review product matching, synonyms, redirects, and catalogue coverage for this search term.';
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

        if ($fixType === 'Part number mapping') {
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