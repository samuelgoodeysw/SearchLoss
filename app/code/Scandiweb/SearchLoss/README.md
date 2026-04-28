# Search Loss Audit

Search Loss Audit is a Magento search audit and diagnosis module.

It helps merchants identify failed site searches, estimate directional revenue at risk, review catalogue evidence, and prioritise what to fix first.

## What it does

Search Loss Audit helps answer:

- What are customers searching for but not finding?
- Which failed searches happen most often?
- Which missed searches may represent the most value?
- Does Magento already contain related product, SKU, category, or catalogue data?
- Is the likely issue catalogue coverage, product visibility, website assignment, category assignment, searchable attributes, synonyms, SKU matching, or search configuration?
- What should the merchant review first?

## Current focus

The current version focuses on failed searches.

A failed search is:

Customer searched -> Magento returned zero results

Search Loss Audit turns failed searches into a prioritised diagnosis list.

## Current features

- Magento admin dashboard
- REST API endpoint
- Failed search term analysis
- Revenue-at-risk estimate
- Opportunity scoring
- Catalogue-aware diagnosis
- Product, SKU, and category signal checks
- Product visibility and assignment evidence
- Suggested Magento fixes
- Expanded diagnostic rows
- CSV export
- Basic bot/noise filtering

## Admin location

Reports -> Business Intelligence -> Search Loss Audit

## REST endpoint

/rest/V1/search-loss/dashboard

## Main files

- app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php
- app/code/Scandiweb/SearchLoss/Model/SearchLoss.php
- app/code/Scandiweb/SearchLoss/Api/SearchLossInterface.php
- app/code/Scandiweb/SearchLoss/etc/webapi.xml
- app/code/Scandiweb/SearchLoss/view/adminhtml/templates/dashboard.phtml

## Data sources

Search Loss Audit currently uses Magento-native data:

- search_query
- sales_order
- catalog_product_entity
- product EAV attributes
- category EAV attributes
- product website assignment
- product category assignment

## Revenue-at-risk model

Revenue at risk is directional, not guaranteed lost revenue.

Current model:

failed search count x average order value x search-to-order rate

Use this as a prioritisation signal, not an exact financial claim.

## Diagnosis examples

Search Loss Audit can classify issues such as:

- Product exists but search is not matching it
- Product exists but is disabled
- Product exists but is not visible in search
- Product exists but is not assigned to website
- Product exists but is not assigned to category
- SKU or part number is not matching
- Brand or product terms are missing
- Customers use different wording
- Spelling or format variant
- Product or category may be missing
- Fitment or use case is unclear
- Search term is too broad or unclear
- Results are weak or badly ranked
- Needs manual review

## Market positioning

Search Loss Audit is not a replacement for Algolia, Klevu, Adobe Live Search, Searchspring, or similar search platforms.

Those tools improve the search experience itself: ranking, relevance, autocomplete, typo tolerance, merchandising, and search result quality.

Search Loss Audit is a diagnostic and opportunity layer. It helps merchants understand where Magento search may be leaking demand, what that missed demand may be worth, and what should be reviewed first.

## Suggested pitch

Search Loss Audit reviews Magento site search and shows which failed searches may represent missed demand, why they may be failing, and what to review first.

## Hyva compatibility

The current module has no storefront frontend footprint.

It is focused on:

- Magento admin
- REST API
- database-read analysis

Future weak-search tracking may require storefront tracking and should be implemented in a Hyva-compatible or theme-neutral way.

## Roadmap

### Phase 1: Failed Searches

Current phase. Identify zero-result searches and diagnose likely causes using Magento search and catalogue data.

### Phase 2: Weak Searches

Future phase. Identify searches that return results but do not lead to meaningful customer engagement.

This likely requires GA4, onsite tracking, or search platform analytics.

### Phase 3: Search Opportunity Control Center

Possible future sections:

- Failed Searches
- Weak Searches
- Catalogue Gaps
- Product Data Health
- Search Configuration Issues
- Merchandising Opportunities
- Revenue Recovery Opportunities

## Known limitations

- Revenue-at-risk is directional.
- Weak-search behaviour is not yet fully tracked.
- GA4 integration is not included in the current phase.
- ERP, margin, inventory, and workflow features are future roadmap items.
- Recommendation logic is rule-based, not LLM-generated.
- Search Loss Audit recommends checks and fixes but does not automatically modify catalogue data.

## Development commands

Check PHP syntax:

php -l app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php
php -l app/code/Scandiweb/SearchLoss/view/adminhtml/templates/dashboard.phtml

Flush cache:

bin/magento cache:flush

Test REST endpoint:

curl -i http://localhost/rest/V1/search-loss/dashboard

Check module status:

bin/magento module:status Scandiweb_SearchLoss

## Packaging notes

Before broader installation or marketplace-style packaging, review:

- version number
- licence
- ACL labels
- admin menu label
- REST API ACL
- customer-facing documentation
- compatibility matrix
- production-safe install instructions

## Performance testing note

Local stress testing was completed using a removable `SLTEST` dataset in Magento's `search_query` table.

Test summary:

- 5,000 additional failed-search rows were inserted for stress testing.
- The API response stayed capped to the prioritized findings set rather than returning every failed term.
- The REST endpoint remained fast locally, responding in roughly 0.5 seconds during the 5,000-row test.
- The response size stayed controlled at around 60 KB.
- The stress rows were removed after testing.

This supports the current MVP approach: Search Loss Audit should behave as a prioritized audit view, not a full raw export of every historical failed search.

For larger production catalogues, the recommended approach is still:

- rank failed searches first
- deep-diagnose only the highest-priority findings
- keep endpoint output capped
- avoid returning every raw search row in the dashboard
