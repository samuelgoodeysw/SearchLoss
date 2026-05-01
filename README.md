# Search Loss Audit

Search Loss Audit is a Magento search audit and diagnosis module.

It helps merchants identify missed search demand, review Magento catalogue evidence, estimate directional commercial value, and prioritise what to review first.

It is not a search engine, not a replacement for Algolia, Klevu, Adobe Live Search, or Searchspring, and not a guaranteed lost-revenue calculator.

## What it does

Search Loss Audit helps answer:

- What are customers searching for but not finding?
- Which failed searches happen most often?
- Which missed searches may represent the most value?
- Does Magento already contain related product, SKU, category, or catalogue data?
- Is the likely issue catalogue coverage, product visibility, website assignment, category assignment, searchable attributes, synonyms, SKU matching, or search configuration?
- Which searches returned results but did not lead to meaningful engagement?
- What should the merchant review first?

## Product positioning

Search Loss Audit is best positioned as:

Search Loss Audit is a Magento search audit and diagnosis tool that shows where customer search demand is being missed, what it may be worth, and what Magento should review first.

The strongest commercial offer is a fixed audit:

A Search Loss Audit that produces a prioritised list of search issues, supporting Magento evidence, and recommended fixes.

Use careful value wording:

- Est. Demand Value
- directional demand value
- estimated demand at risk
- potential search opportunity

Avoid:

- guaranteed lost revenue
- exact lost revenue
- proven recovery

## Diagnostic layers

### Phase 1: Failed Searches

A failed search is:

    Customer searched -> Magento returned zero results

This layer uses Magento-native data and does not require GA4.

It helps diagnose:

- missing product or category coverage
- poor product/category naming
- SKU or part-number matching issues
- missing synonyms or search terms
- product visibility/status issues
- website/category assignment issues
- searchable attribute gaps
- stock or salability review areas
- search configuration problems

### Phase 2: Searches With Low Engagement

A low-engagement search is:

    Customer searched -> results or page activity existed -> customer did not meaningfully move forward

This layer uses GA4 enrichment where the store actually tracks usable data.

It helps diagnose:

- weak search result relevance
- poor ranking
- low product engagement after search
- low add-to-cart after product views
- low purchase after add-to-cart
- product-page confidence issues
- search terms that drive revenue and should be monitored, not treated as loss items

GA4 is optional enrichment. Search Loss Audit should still work as a Magento failed-search audit if GA4 data is not available.

## Current features

- Magento admin dashboard
- REST API endpoint
- failed search term analysis
- Est. Demand Value calculation
- opportunity scoring
- catalogue-aware diagnosis
- product, SKU, category, and identity attribute signal checks
- product visibility and assignment evidence
- searchable attribute evidence
- stock evidence
- suggested Magento fixes
- expanded diagnostic rows
- CSV export
- basic bot/noise filtering
- Configure audit panel
- configurable identity attributes
- configurable ignored terms
- configurable minimum failed-search volume
- real GA4 probe command
- real GA4 sync command
- GA4 search URL q parameter extraction
- GA4 searchTerm support where available
- GA4 ecommerce engagement metrics where available
- configurable GA4 low-engagement thresholds

## Admin location

Reports -> Business Intelligence -> Search Loss Audit

## REST endpoint

/rest/V1/search-loss/dashboard

The endpoint returns key/value blocks including:

- searchData
- failedSearchTerms
- lowEngagementSearchTerms

## Main files

- app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php
- app/code/Scandiweb/SearchLoss/Model/SearchLoss.php
- app/code/Scandiweb/SearchLoss/Api/SearchLossInterface.php
- app/code/Scandiweb/SearchLoss/etc/webapi.xml
- app/code/Scandiweb/SearchLoss/view/adminhtml/templates/dashboard.phtml
- app/code/Scandiweb/SearchLoss/Model/Ga4/Probe.php
- app/code/Scandiweb/SearchLoss/Model/Ga4/Sync.php
- app/code/Scandiweb/SearchLoss/Console/Command/Ga4ProbeCommand.php
- app/code/Scandiweb/SearchLoss/Console/Command/Ga4SyncCommand.php

## Data sources

Search Loss Audit currently uses Magento-native data such as:

- search_query
- sales_order
- catalog product EAV tables
- category EAV tables
- product website assignment
- product category assignment
- product status
- product visibility
- stock status
- searchable/catalog attribute configuration
- configured product identity attributes

For GA4 enrichment, Search Loss Audit uses:

- GA4 searchTerm where available
- Magento search-result URLs such as /catalogsearch/result/?q=...
- GA4 engagement/ecommerce metrics where attributable

## Est. Demand Value model

Est. Demand Value is directional, not guaranteed lost revenue.

Current model:

    failed search count x average order value x search-to-order rate

Use this as a prioritisation signal, not an exact financial claim.

## GA4 enrichment

GA4 enrichment is probe-first.

Before relying on Phase 2, run:

    bin/magento searchloss:ga4:probe today today

The probe checks:

- whether GA4 is enabled
- whether property/authentication is configured
- whether GA4 search terms are available
- whether Magento search-result URLs can be queried and parsed
- whether engagement/ecommerce metrics are attributable by search term

If the probe passes, run:

    bin/magento searchloss:ga4:sync today today

The sync writes rows into:

    scandiweb_searchloss_ga4_term

Search Loss Audit should not show guessed GA4 engagement data. Probe first; enable only what the store actually tracks.

## Configurable low-engagement thresholds

The Configure audit panel includes GA4 threshold settings:

- Minimum searches for low-engagement flag
- Low product engagement below (%)
- Low add-to-cart below (%)
- Low purchase below (%)
- Healthy purchase signal at (%)

Default behaviour:

- Minimum searches for low-engagement flag: 5
- Low product engagement below: 20%
- Low add-to-cart below: 10%
- Low purchase below: 3%
- Healthy purchase signal at: 5%

These thresholds are intentionally configurable because different stores have different traffic levels, buying cycles, and expectations.

## Diagnosis examples

Search Loss Audit can classify issues such as:

- Product exists but search is not matching it
- Product exists but is disabled
- Product exists but is not visible in search
- Product exists but is not assigned to website
- Product exists but is not assigned to category
- Product exists but may be out of stock
- SKU or part number is not matching
- Brand or product terms are missing
- Customers use different wording
- Spelling or format variant
- Product or category may be missing
- Fitment or use case is unclear
- Search term is too broad or unclear
- Results are weak or badly ranked
- High searches, low product engagement
- Product views, low add-to-cart
- Add-to-cart, low purchase
- Healthy revenue signal
- Needs manual review

## Market positioning

Search Loss Audit is not a replacement for Algolia, Klevu, Adobe Live Search, Searchspring, or similar search platforms.

Those tools improve the search experience itself: ranking, relevance, autocomplete, typo tolerance, merchandising, and search result quality.

Search Loss Audit is a diagnostic and opportunity layer. It helps merchants understand where Magento search may be leaking demand, what that missed demand may be worth, and what should be reviewed first.

It can be useful before, during, or after a larger search improvement project.

## Suggested pitch

Search Loss Audit reviews Magento site search and shows which search issues may represent missed demand, why they may be happening, and what to review first.

A slightly fuller version:

Search Loss Audit uses Magento data, and optional GA4 enrichment, to identify failed searches and weak searches, check supporting catalogue evidence, estimate directional demand value, and produce a prioritised review list.

## Hyva compatibility

The current admin/audit functionality is focused on:

- Magento admin
- REST API
- database-read analysis

The GA4 frontend tag is optional and should be treated as validation/enrichment rather than core storefront functionality.

If future tracking is expanded, it should remain Hyva-compatible or theme-neutral.

## Known limitations

- Est. Demand Value is directional.
- GA4 enrichment depends on the store's actual GA4 tracking quality.
- GA4 search terms and engagement metrics must be validated with the probe.
- Some stores may have limited or noisy Magento search_query history.
- Third-party search platforms may not populate Magento native search data fully.
- Recommendation logic is rule-based, not LLM-generated.
- Search Loss Audit recommends checks and fixes but does not automatically modify catalogue data.
- The module has been validated locally, but broader client install hardening is still needed.

## Development commands

Check PHP syntax:

    php -l app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php
    php -l app/code/Scandiweb/SearchLoss/Block/Adminhtml/Dashboard.php
    php -l app/code/Scandiweb/SearchLoss/Controller/Adminhtml/Config/Save.php
    php -l app/code/Scandiweb/SearchLoss/Controller/Adminhtml/Export/All.php
    php -l app/code/Scandiweb/SearchLoss/view/adminhtml/templates/dashboard.phtml
    php -l app/code/Scandiweb/SearchLoss/Console/Command/Ga4ProbeCommand.php
    php -l app/code/Scandiweb/SearchLoss/Console/Command/Ga4SyncCommand.php
    php -l app/code/Scandiweb/SearchLoss/Model/Ga4/Probe.php
    php -l app/code/Scandiweb/SearchLoss/Model/Ga4/Sync.php

Flush cache:

    bin/magento cache:flush

Test REST endpoint:

    curl -i http://localhost/rest/V1/search-loss/dashboard

Probe GA4:

    bin/magento searchloss:ga4:probe today today

Sync GA4:

    bin/magento searchloss:ga4:sync today today

Compile:

    rm -rf generated/code/* generated/metadata/* var/cache/* var/page_cache/* var/di/*
    bin/magento setup:di:compile
    bin/magento cache:flush

## Packaging notes

Before broader installation or marketplace-style packaging, review:

- composer install flow
- module version
- licence
- ACL labels
- admin menu labels
- REST API ACL
- customer-facing documentation
- compatibility matrix
- production-safe install instructions
- admin shortcut safety
- no test tokens or local-only credentials
- no local-only permission guidance
- MSI/salability handling for larger Adobe Commerce installs

## Performance testing note

Local stress testing was completed using a removable SLTEST dataset in Magento's search_query table.

Test summary:

- 5,000 additional failed-search rows were inserted for stress testing.
- The API response stayed capped to the prioritized findings set rather than returning every failed term.
- The REST endpoint remained fast locally, responding in roughly 0.5 seconds during the 5,000-row test.
- The response size stayed controlled at around 60 KB.
- The stress rows were removed after testing.

This supports the current MVP approach:

- rank failed searches first
- deep-diagnose only the highest-priority findings
- keep endpoint output capped
- avoid returning every raw search row in the dashboard

## Commercial readiness

Search Loss Audit is closest to being sold as a fixed audit/service offer, not as a self-serve extension yet.

Recommended near-term offer:

Fixed Search Loss Audit

Includes:

- setup or dashboard review
- failed-search analysis
- optional GA4 enrichment if available
- top missed-demand opportunities
- catalogue/search diagnosis
- prioritised fix list
- client review session or written summary

This can open follow-up work around:

- search configuration
- catalogue data cleanup
- synonyms/search terms
- product visibility/assignment fixes
- GA4/search tracking
- Adobe Live Search or search platform feasibility
- workflow or reporting automation
