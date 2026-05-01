<?php

namespace Scandiweb\SearchLoss\Model\Ga4;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

class Sync
{
    private const XML_PATH_ENABLED = 'searchloss/ga4/enabled';
    private const XML_PATH_PROPERTY_ID = 'searchloss/ga4/property_id';
    private const XML_PATH_CREDENTIALS_JSON = 'searchloss/ga4/credentials_json';

    public function __construct(
        private ResourceConnection $resource,
        private ScopeConfigInterface $scopeConfig
    ) {}

    public function execute(string $startDate, string $endDate): int
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED)) {
            throw new \RuntimeException(
                'GA4 sync is disabled. Enable it in Stores > Configuration > Scandiweb > Search Loss Audit > GA4 Integration.'
            );
        }

        $propertyId = trim((string)$this->scopeConfig->getValue(self::XML_PATH_PROPERTY_ID));
        $credentialsJson = trim((string)$this->scopeConfig->getValue(self::XML_PATH_CREDENTIALS_JSON));

        if ($propertyId === '' || strtolower($propertyId) === 'test') {
            throw new \RuntimeException(
                'GA4 property ID is missing or still set to a placeholder. Configure a real GA4 property ID before syncing.'
            );
        }

        if ($credentialsJson === '' || $credentialsJson === '{}') {
            throw new \RuntimeException(
                'GA4 service account credentials are missing. Configure real service account JSON before syncing.'
            );
        }

        $credentials = json_decode($credentialsJson, true);

        if (!is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new \RuntimeException(
                'GA4 service account credentials JSON is invalid. It must include client_email and private_key.'
            );
        }

        throw new \RuntimeException(
            'Real GA4 sync is not implemented yet. Run bin/magento searchloss:ga4:probe first to confirm whether this property supports Level 1 or Level 2 Search Loss data.'
        );
    }
}
