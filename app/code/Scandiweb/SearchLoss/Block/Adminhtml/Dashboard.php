<?php

namespace Scandiweb\SearchLoss\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\App\ResourceConnection;

class Dashboard extends Template
{
    protected ResourceConnection $resource;

    public function __construct(
        Template\Context $context,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->resource = $resource;
        parent::__construct($context, $data);
    }

    public function getCurrentPeriod(): string
    {
        return (string)($this->getRequest()->getParam('period') ?: 'all');
    }

    private function applyDateFilter($select)
    {
        $period = $this->getCurrentPeriod();

        if ($period === '7') {
            $select->where('updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
        }

        if ($period === '30') {
            $select->where('updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
        }

        return $select;
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

    private function getConversionRate(): float
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from('search_query', ['total' => 'SUM(popularity)']);

        $this->applyDateFilter($select);

        $searches = (float)$connection->fetchOne($select);

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

    public function getFailedSearchTerms(): array
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from('search_query', ['query_text', 'num_results', 'popularity', 'updated_at'])
            ->where('num_results = 0')
            ->order('popularity DESC')
            ->limit(20);

        $this->applyDateFilter($select);

        $terms = $connection->fetchAll($select);

        $aov = $this->getAverageOrderValue();
        $conversionRate = $this->getConversionRate();

        foreach ($terms as &$term) {
            $term['lost_revenue'] = (float)$term['popularity'] * $aov * $conversionRate;
        }

        usort($terms, function ($a, $b) {
            return $b['lost_revenue'] <=> $a['lost_revenue'];
        });

        return $terms;
    }

    public function getWeakSearchTerms(): array
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from('search_query', ['query_text', 'num_results', 'popularity', 'updated_at'])
            ->where('num_results > 0')
            ->order('popularity DESC')
            ->limit(20);

        $this->applyDateFilter($select);

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

    public function getSummary(): array
    {
        $failed = $this->getFailedSearchTerms();

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
            'conversion_rate' => $this->getConversionRate() * 100,
        ];
    }
}
