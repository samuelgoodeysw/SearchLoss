<?php

namespace Scandiweb\SearchLoss\Model;

class SearchLossDataProvider
{
    public function getDashboardData(): array
    {
        return [
            [
                'key' => 'searchData',
                'value' => [
                    'totalSearches' => 31245,
                    'zeroResultRate' => 9.8,
                    'searchToOrderRate' => 18.4,
                    'modeledDemandLost' => 48200
                ]
            ],
            [
                'key' => 'failedSearchTerms',
                'value' => [
                    [
                        'term' => 'coilover kit',
                        'count' => 428,
                        'conversion' => 0,
                        'lostRevenue' => 9200,
                        'opportunityScore' => 'High',
                        'suggestedFix' => 'Create redirect to suspension kits',
                        'trend' => 'up'
                    ],
                    [
                        'term' => 'brake upgrade',
                        'count' => 312,
                        'conversion' => 0,
                        'lostRevenue' => 7100,
                        'opportunityScore' => 'High',
                        'suggestedFix' => 'Add synonym for performance brakes',
                        'trend' => 'up'
                    ],
                    [
                        'term' => 'control arm bushing',
                        'count' => 245,
                        'conversion' => 1.1,
                        'lostRevenue' => 5400,
                        'opportunityScore' => 'Medium',
                        'suggestedFix' => 'Improve product tagging',
                        'trend' => 'up'
                    ],
                    [
                        'term' => 'turbo kit',
                        'count' => 221,
                        'conversion' => 0.8,
                        'lostRevenue' => 12800,
                        'opportunityScore' => 'High',
                        'suggestedFix' => 'Create collection/landing page',
                        'trend' => 'down'
                    ],
                    [
                        'term' => 'lowering springs',
                        'count' => 194,
                        'conversion' => 1.5,
                        'lostRevenue' => 4300,
                        'opportunityScore' => 'Medium',
                        'suggestedFix' => 'Add synonym for lowering suspension',
                        'trend' => 'up'
                    ],
                    [
                        'term' => 'wheel spacers',
                        'count' => 166,
                        'conversion' => 0,
                        'lostRevenue' => 3100,
                        'opportunityScore' => 'Low',
                        'suggestedFix' => 'Map common fitment phrase',
                        'trend' => 'stable'
                    ]
                ]
            ]
        ];
    }
}