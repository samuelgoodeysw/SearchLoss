<?php

namespace Scandiweb\SearchLoss\Model\Ga4;

use Magento\Framework\App\ResourceConnection;

class Sync
{
    public function __construct(
        private ResourceConnection $resource
    ) {}

    public function execute(string $startDate, string $endDate): int
    {
        $connection = $this->resource->getConnection();

        // Fake GA4-style data (simulate API response)
        $data = [
            ['term' => 'leaf spring', 'searches' => 120, 'views' => 80, 'carts' => 30, 'purchases' => 10, 'revenue' => 2500],
            ['term' => 'bolt', 'searches' => 90, 'views' => 50, 'carts' => 10, 'purchases' => 3, 'revenue' => 600],
            ['term' => 'trailer axle', 'searches' => 60, 'views' => 20, 'carts' => 5, 'purchases' => 1, 'revenue' => 300],
            ['term' => 'air suspension', 'searches' => 150, 'views' => 100, 'carts' => 40, 'purchases' => 12, 'revenue' => 3200],
        ];

        $count = 0;

        foreach ($data as $row) {
            $connection->insertOnDuplicate(
                'scandiweb_searchloss_ga4_term',
                [
                    'report_date' => date('Y-m-d'),
                    'search_term' => $row['term'],
                    'searches' => $row['searches'],
                    'product_views' => $row['views'],
                    'add_to_carts' => $row['carts'],
                    'purchases' => $row['purchases'],
                    'revenue' => $row['revenue'],
                ],
                ['searches','product_views','add_to_carts','purchases','revenue']
            );

            $count++;
        }

        return $count;
    }
}
