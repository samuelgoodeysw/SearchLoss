# Search Loss for Magento 2

Search Loss is a Magento-native admin module that helps merchants identify where site search is leaking demand.

It finds failed searches, estimates revenue at risk, checks Magento catalogue signals, and recommends practical review steps so teams can decide what to fix first.

## What Search Loss helps answer

- What are customers searching for but not finding?
- Which failed searches happen most often?
- Which failed searches may represent the most revenue at risk?
- Does Magento already contain related product, SKU, or category data?
- Is the issue likely to be missing catalogue coverage, weak product data, missing synonyms, SKU matching, or search configuration?
- Which fixes should be reviewed first?

## Product positioning

Search Loss is not a replacement for search platforms such as Algolia, Klevu, Adobe Live Search, Searchspring, or similar tools.

Those tools mainly improve the search experience itself through relevance, autocomplete, typo tolerance, merchandising, ranking, and behavioural analytics.

Search Loss is different.

It is a diagnostic and opportunity layer for Magento. It shows where search may be failing, what that missed demand may be worth, what Magento catalogue evidence suggests, and what the merchant should review first.

## Current module status

Current admin location:

```text
Reports -> Search Loss
```

Current module path:

```text
app/code/Scandiweb/SearchLoss
```

Main data provider:

```text
app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php
```

Main admin template:

```text
app/code/Scandiweb/SearchLoss/view/adminhtml/templates/dashboard.phtml
```

REST endpoint:

```text
/rest/V1/search-loss/dashboard
```

The same `SearchLossDataProvider` powers both the Magento admin dashboard and the REST API response, which keeps the admin UI and external dashboard data aligned.

## Current features

The Magento admin dashboard currently includes:

- Hero section explaining the failed-search opportunity
- KPI cards
- Commercial insight cards
- Top opportunity summary
- Period controls
- Ranked opportunities table
- Search/filter bar
- Priority filter
- Issue type filter
- Sort dropdown
- Clickable sortable table headers
- Reset button
- CSV export
- Expandable diagnostic rows
- Tooltips across KPI, table, and catalogue signal areas
- Compact shortcuts inside the recommended fix area

## KPI cards

The top KPI cards show:

- Failed Terms
- Failed Searches
- Revenue at Risk
- Average Order Value
- Search-to-order Rate

Revenue at risk is directional. It is not guaranteed lost revenue.

Current model:

```text
failed search count x average order value x search-to-order rate
```

## Opportunity table

The main table shows:

- Term
- Searches
- Revenue at Risk
- Priority
- Issue Type
- Confidence
- Suggested Action
- Details

The table is designed as the scan layer. It helps a merchant quickly see which search issues are most important.

The expanded row is the detail layer. It explains the diagnosis, catalogue evidence, recommended fix, and useful review shortcuts.

## Expanded diagnostic rows

Each opportunity can be expanded.

Expanded rows currently contain three main areas:

1. Diagnosis
2. Catalogue Signal
3. Recommended Fix

### Diagnosis

Plain-English explanation of what the failed search likely means.

### Catalogue Signal

Magento evidence showing whether related catalogue data appears to exist.

Current catalogue signal fields include:

- Search words checked
- SKU matches
- Full-phrase product matches
- Keyword product matches
- Full-phrase category matches
- Keyword category matches
- Catalogue signal
- What this suggests

This matters because a failed search can mean different things.

If Magento returned zero results and no related catalogue data was found, it may indicate a true catalogue gap.

If Magento returned zero results but related catalogue data was found, it may indicate weak product naming, missing synonyms, poor searchable attributes, indexing issues, or search configuration problems.

### Recommended Fix

Shows the full recommended review/fix steps.

This section also includes compact shortcuts such as:

- Products
- Categories
- Search terms
- Indexes
- Cache

These are review shortcuts only. Search Loss does not automatically change catalogue, checkout, order, customer, search, or storefront data.

## Current data sources

The module currently uses Magento-native data:

- `search_query`
- `sales_order`
- `catalog_product_entity`
- `catalog_product_entity_varchar`
- `catalog_category_entity_varchar`
- `eav_attribute`
- `eav_entity_type`

Current usage:

- `search_query` provides failed search terms, popularity, result count, and update time.
- `sales_order` helps estimate average order value and order count.
- Product/category EAV data helps detect catalogue signal evidence.
- SKU, product, and category matching support diagnosis and recommendations.

## Current recommendation logic

The recommendation logic is rule-based and Magento-aware.

Current issue types include:

- Product exists but is not showing
- Product or category may be missing
- Brand or product terms are missing
- Customers use different wording
- SKU or part number is not matching
- Spelling or format variant
- Fitment or use case is unclear
- Search term is too broad or unclear
- Results are weak or badly ranked
- Needs manual review

The module checks catalogue data before presenting recommendations. This makes the output more useful than generic search advice.

## Current REST API

Endpoint:

```text
/rest/V1/search-loss/dashboard
```

Example response structure:

```json
[
  {
    "key": "searchData",
    "value": {
      "totalSearches": 335,
      "failedSearches": 156,
      "zeroResultRate": 46.57,
      "searchToOrderRate": 6.27,
      "averageOrderValue": 424.33,
      "modeledDemandLost": 4149.6
    }
  },
  {
    "key": "failedSearchTerms",
    "value": [
      {
        "term": "boat trailer axle",
        "count": 21,
        "lostRevenue": 558.6,
        "opportunityScore": "High",
        "fixType": "Product or category may be missing",
        "suggestedFix": "Check whether the store sells this product, an equivalent product, or a close substitute.",
        "confidence": "High",
        "trend": "up"
      }
    ]
  }
]
```

## CSV export

The dashboard can export the current filtered/sorted opportunity set.

Exported fields include:

- Term
- Searches
- Revenue at Risk
- Priority
- Issue Type
- Confidence
- Suggested Action

Revenue is exported as a raw number rather than formatted currency so it remains easier to sort, filter, and sum in spreadsheets.

## Hyva compatibility

The current module should be compatible with Hyva because it has no storefront frontend footprint.

The module is:

- Magento Admin focused
- REST API focused
- Database-read focused

It does not currently depend on:

- Luma frontend templates
- Knockout storefront components
- RequireJS storefront behaviour
- Magento frontend UI components
- Checkout frontend code

Current storefront footprint check should return no output:

```bash
find app/code/Scandiweb/SearchLoss \
  -path "*view/frontend*" \
  -o -path "*view/base*" \
  -o -name "requirejs-config.js" \
  -o -name "*.js" \
  -o -name "*.less" \
  -o -name "*.css"
```

Future weak-search tracking may require storefront tracking. If added, it should be implemented in a Hyva-compatible or theme-neutral way.

## Hyva Commerce note

The module should also be safe for Hyva Commerce in principle because it does not modify the storefront.

The main thing to visually test later is compatibility with any Hyva Commerce admin theme styling, because the Search Loss admin page uses custom admin markup and styling.

## Installation notes

This module currently lives inside a Magento codebase at:

```text
app/code/Scandiweb/SearchLoss
```

Typical local development commands:

```bash
cd /home/magento/magento

bin/magento module:status Scandiweb_SearchLoss
bin/magento cache:flush
```

Syntax checks:

```bash
php -l app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php
php -l app/code/Scandiweb/SearchLoss/view/adminhtml/templates/dashboard.phtml
```

Test REST endpoint:

```bash
curl -i http://localhost/rest/V1/search-loss/dashboard
```

Expected result:

```text
HTTP/1.1 200 OK
Content-Type: application/json
```

## Local development caution

The local Magento environment may hit ownership or permission issues after cache, compile, or static asset operations.

Typical error:

```text
cache_dir "/home/magento/magento/var/cache/" is not writable
```

Local-only workaround:

```bash
sudo chmod -R 777 /home/magento/magento/var
sudo chmod -R 777 /home/magento/magento/generated
sudo chmod -R 777 /home/magento/magento/pub/static
sudo chmod -R 777 /home/magento/magento/pub/media
php bin/magento cache:flush
```

This is for local development only and is not production-safe.

## Useful development commands

Check module files:

```bash
find app/code/Scandiweb/SearchLoss -maxdepth 4 -type f | sort
```

Check module status:

```bash
bin/magento module:status Scandiweb_SearchLoss
```

Flush cache:

```bash
bin/magento cache:flush
```

Clear generated admin view/static files after admin UI changes:

```bash
sudo rm -rf var/view_preprocessed/* pub/static/adminhtml/*
bin/magento cache:flush
```

Check recent Git history:

```bash
git log --oneline -5
```

Check working tree:

```bash
git status
```

## Repository separation

Keep Magento module work and standalone dashboard work separate.

Magento module repo:

```text
https://github.com/samuelgoodeysw/SearchLoss
```

External dashboard repo:

```text
https://github.com/samuelgoodeysw/SearchLossDashboard
```

Do not push Search Loss dashboard work to the old `SummitChassisSupply` repo.

## Commercial positioning

Search Loss works well as a low-friction Magento search audit or productized diagnostic.

Possible offer:

```text
Fixed Search Loss Audit
```

Includes:

- Module install
- Dashboard review
- Top failed search opportunities
- Prioritised fix list
- Written summary or client review session

This can open the door to larger work such as:

- Search/catalogue fixes
- GA4 integration
- Weak-search tracking
- Hyva-safe storefront tracking
- ERP/order/margin integration
- Algolia, Klevu, Adobe Live Search, or Searchspring implementation
- Broader commerce opportunity dashboards

## Roadmap

### Phase 1: Failed Searches

Current phase.

A failed search is:

```text
Customer searched -> Magento returned zero results
```

This identifies:

- Missing catalogue coverage
- Poor product/category naming
- Missing synonyms
- SKU or part-number matching issues
- Search configuration problems
- Product data issues
- Category routing opportunities

### Phase 2: Weak Searches

Future phase.

A weak search is:

```text
Customer searched -> results were returned -> customer did not meaningfully engage
```

This could identify:

- Poor result quality
- Bad ranking
- Irrelevant products shown
- Weak product data
- Low product click-through
- Low add-to-cart after search
- Low purchase conversion after search

This likely requires GA4, onsite tracking, or search platform analytics because Magento native `search_query` data does not reliably show product clicks, add-to-cart behaviour, or purchases after search.

### Phase 3: Search Opportunity Control Center

Search Loss could become the first section inside a broader Magento admin control center.

Possible future sections:

- Failed Searches
- Weak Searches
- Catalogue Gaps
- Product Data Health
- Search Configuration Issues
- Merchandising Opportunities
- Revenue Recovery Opportunities

## Next best improvements

### Add product visibility evidence

Useful checks:

- Product enabled/disabled
- Visibility setting
- Website assignment
- Stock status
- Category assignment
- Searchable attribute configuration

This would allow stronger diagnoses such as:

- Product exists but is disabled
- Product exists but is not visible in search
- Product exists but is not assigned to this website
- Product exists but is out of stock
- Product exists but search is not using the right attributes

### Improve catalogue evidence

Potential additions:

- Product descriptions
- Short descriptions
- Manufacturer/brand attributes
- MPN attributes
- Custom part number attributes
- Product status
- Product visibility
- Stock status
- Website assignment

### Add deeper order evidence

Potential additions:

- Average order value by selected period
- Related category revenue
- Related product revenue
- Related units sold
- Recent demand for related products
- Order value attached to product/category matches

This would make Search Loss more commercially convincing.

Example:

```text
Related axle/suspension products generated $8,240 in recent orders, so this failed search may represent real buying demand.
```

## Package readiness checklist

Before sharing or packaging, review:

- `composer.json`
- `module.xml` version
- README
- ACL labels
- Admin menu label
- REST API ACL
- No test tokens
- No dev-only wording in production docs
- No broken markdown
- No unnecessary frontend assets
- No production-unsafe permission instructions outside local development notes

## Current summary

Search Loss helps merchants move from:

```text
We have failed searches.
```

to:

```text
These are the missed search opportunities that matter most, here is what they may be worth, here is what Magento data suggests, and here is what to fix first.
```
