# Search Loss

Search Loss is a Magento 2 admin module that helps merchants find missed revenue opportunities caused by failed site searches.

A failed search is a customer search that returned zero Magento results.

Search Loss turns those failed searches into a prioritized action list by showing:

- what customers searched for
- how often the search failed
- estimated revenue at risk
- likely issue type
- confidence level
- catalog evidence from Magento
- recommended Magento checks and fixes

The goal is simple:

Show where Magento search is silently losing demand, estimate the commercial value, and tell the merchant what to fix first.

---

## What Search Loss Does

Search Loss analyzes Magento search data and highlights missed demand.

It helps answer questions such as:

- Which customer searches returned zero results?
- Which failed searches happen most often?
- Which failed searches may represent the most revenue at risk?
- Does Magento already have related product, SKU, or category data?
- Is the issue likely a catalog gap, product data issue, synonym issue, SKU issue, or search configuration issue?
- What should the merchant review or fix first?

---

## Current Admin Location

Search Loss is available inside Magento Admin.

Current location:

Reports -> Search Loss

The admin page includes:

- dashboard summary cards
- insight cards
- top opportunity card
- ranked search opportunities table
- search/filter controls
- priority filter
- issue type filter
- sort dropdown
- clickable table sorting
- reset button
- CSV export
- expandable diagnostic rows
- tooltip explanations

---

## Current Features

### Failed Search Detection

Search Loss reads Magento search query data and identifies customer searches that returned zero results.

It uses Magento's `search_query` table.

Important fields include:

- `query_text`
- `num_results`
- `popularity`
- `updated_at`

A failed search is currently defined as:

- `num_results = 0`

---

### Revenue at Risk

Search Loss estimates revenue at risk for failed searches.

The model currently uses:

- failed search count
- average order value
- search-to-order rate

This is a directional estimate, not guaranteed lost revenue.

The purpose is to help merchants prioritize which failed searches are commercially worth reviewing first.

---

### Opportunity Ranking

Each failed search is ranked using:

- search demand
- estimated revenue at risk
- likely issue type
- confidence level

Opportunity priority is currently shown as:

- High
- Medium
- Low

---

### Issue Type Classification

Search Loss assigns a likely issue type to each failed search.

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

The recommendation logic is currently rule-based and Magento-aware.

---

### Catalog Signal Evidence

Expanded rows include a Catalog Signal section.

This checks whether the failed search term appears to have related Magento catalog data.

Current checks include:

- search words checked
- SKU matches
- full-phrase product matches
- keyword product matches
- full-phrase category matches
- keyword category matches
- catalog signal summary
- what the signal suggests

This helps separate two different problems:

1. Magento returned zero results and no related catalog signal exists  
   This may indicate a missing product, missing category, or true catalog demand gap.

2. Magento returned zero results but related catalog data exists  
   This may indicate weak product naming, missing synonyms, poor searchable attributes, indexing issues, or search ranking problems.

This is what makes Search Loss more useful than a simple zero-results report.

---

### Recommended Fixes

Each opportunity includes a short action in the main table and a fuller recommendation inside the expanded row.

The expanded row currently includes:

- Diagnosis
- Catalog Signal
- Recommended Fix

Recommended fixes may include:

- confirm whether the store sells the searched product
- improve product naming
- improve searchable attributes
- review SKU and part-number matching
- add safe synonyms
- route customers to the closest category or landing page
- treat repeated searches as catalog demand
- reindex Magento search and test again

---

### Filters, Sorting, and Export

The opportunities table includes:

- search term filter
- priority filter
- issue type filter
- sort dropdown
- clickable sortable headers
- reset filters
- CSV export

CSV export is useful for:

- sharing the opportunity list
- reviewing with merchandising teams
- creating tickets
- prioritizing fixes
- using the data in spreadsheets

---

## Market Positioning

Search Loss is not intended to replace dedicated search platforms such as Algolia, Klevu, Adobe Live Search, or similar tools.

Those platforms mainly focus on improving the search experience itself, including:

- relevance
- autocomplete
- typo tolerance
- merchandising
- product ranking
- click behavior
- conversion analytics

Search Loss is different.

Search Loss is a Magento-native diagnostic and opportunity layer. Its purpose is to show where Magento search may be leaking demand, estimate the commercial value of those missed searches, and help merchants understand what to fix first.

In simple terms:

Magento native search reports give basic search term visibility.

Algolia, Klevu, Adobe Live Search, and similar platforms provide search engine functionality, ranking, merchandising, and search analytics.

Search Loss provides missed-demand diagnosis, revenue opportunity scoring, catalog evidence, and Magento fix recommendations.

Search Loss is best positioned as a low-friction diagnostic layer before, during, or after a larger search improvement project.

It can help a merchant decide whether they need:

- better product data
- better category routing
- synonym improvements
- searchable attribute changes
- catalog expansion
- search configuration fixes
- or a larger search platform implementation

This makes Search Loss useful even when a merchant later adopts a third-party search tool. It identifies the missed demand and commercial opportunity behind the search problem.

---

## Why This Is Useful

Many merchants know search matters, but they do not always know where search is failing.

Magento may already record failed search terms, but raw search term data is not enough.

Search Loss adds the commercial and operational layer:

- how often the search failed
- what it might be worth
- whether the catalog has related data
- what kind of issue it likely is
- what the merchant should review first

The product is designed to move from analytics to action.

---

## Current Data Sources

Search Loss currently uses Magento data.

Current sources include:

- Magento `search_query` table
- Magento order data
- Magento product data
- Magento category data
- Magento EAV product/category name attributes

Used for:

- failed search terms
- search popularity
- date filtering
- average order value
- revenue-at-risk modeling
- SKU matching
- product-name matching
- category-name matching
- related keyword matching

---

## Current Architecture

Search Loss currently has a shared Magento data provider.

Main provider:

`app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php`

The provider is used by:

- the Magento admin dashboard
- the Search Loss REST API endpoint

This keeps the admin page and API response aligned.

---

## REST API Endpoint

Search Loss exposes a Magento REST endpoint.

Endpoint:

`/rest/V1/search-loss/dashboard`

This endpoint returns Search Loss dashboard data including:

- search summary data
- failed search opportunities
- revenue-at-risk values
- issue types
- suggested fixes
- confidence
- catalog evidence
- Magento fix steps

The endpoint can be used by an external dashboard or client portal if required.

---

## External Dashboard Compatibility

The Search Loss module can support an external dashboard.

The intended architecture is:

Magento Search Loss module  
-> Magento REST endpoint  
-> external API route  
-> external dashboard

This allows Search Loss data to be shown outside Magento when needed.

For Magento-only clients, the native Magento module is likely the strongest product experience.

For multi-platform clients, or clients who want a separate portal, the external dashboard can provide a wider view across systems.

---

## Future Roadmap

### Phase 1: Failed Searches

This is the current focus.

A failed search is when:

Customer searched -> Magento returned zero results

This identifies:

- missing catalog coverage
- poor product/category naming
- missing synonyms
- SKU or part-number matching issues
- search configuration problems
- product data issues
- category routing opportunities

---

### Phase 2: Weak Searches

A future phase can add Weak Searches.

A weak search is when:

Customer searched -> results were returned -> customer did not meaningfully engage

Weak Searches could identify:

- poor result quality
- bad ranking
- irrelevant products shown
- weak product data
- low product click-through
- low add-to-cart after search
- low purchase conversion after search

This would likely require GA4, onsite tracking, or search platform analytics because Magento's native search query data does not reliably show product clicks, add-to-cart behavior, or purchases after search.

---

### Phase 3: Search Opportunity Control Center

Search Loss could later become part of a broader commerce opportunity control center.

Possible future sections:

- Failed Searches
- Weak Searches
- Catalog Gaps
- Product Data Health
- Search Configuration Issues
- Merchandising Opportunities
- Revenue Recovery Opportunities

The larger product idea is:

A Magento admin control center that shows where demand is leaking, what it may be worth, and what the merchant should fix first.

---

## Installation Notes

This module is currently under active development.

Typical Magento module path:

`app/code/Scandiweb/SearchLoss`

After adding or changing module files, run the usual Magento setup and cache commands as needed:

- `bin/magento setup:upgrade`
- `bin/magento setup:di:compile`
- `bin/magento cache:flush`

Depending on the environment, static content deployment may also be required.

---

## Development Notes

Useful checks:

- `php -l app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php`
- `php -l app/code/Scandiweb/SearchLoss/view/adminhtml/templates/dashboard.phtml`
- `git status`
- `git log --oneline -5`

Useful Magento cache command:

- `bin/magento cache:flush`

Useful endpoint test:

- `curl -i http://localhost/rest/V1/search-loss/dashboard`

---

## Known Limitations

Current limitations:

- Recommendations are rule-based, not AI-generated.
- Revenue at risk is directional, not guaranteed lost revenue.
- Weak search analysis is not included yet.
- Click-through and add-to-cart behavior are not available from native Magento search query data alone.
- Catalog evidence currently checks product names, category names, SKU signals, and keyword matches. Deeper attribute matching can be added later.
- Stock, margin, inventory, and ERP data are not currently included.
- GA4 integration is not currently included.

---

## Future Data Sources

Potential future integrations:

- GA4
- Google Search Console
- Shopify
- ERP systems such as NetSuite
- inventory data
- margin data
- search platform analytics
- onsite product click tracking

These would make Search Loss more powerful by connecting failed or weak search demand to real behavior, product availability, margin, and conversion outcomes.

---

## Product Summary

Search Loss is a Magento-native search opportunity engine.

It helps merchants move from:

"We have failed searches"

to:

"These are the missed search opportunities that matter most, here is what they may be worth, here is what Magento data suggests, and here is what to fix first."

## Hyvä Compatibility

The current Search Loss module is Magento Admin and REST API focused.

It does not currently include storefront templates, frontend layout XML, RequireJS frontend configuration, Knockout UI components, LESS/CSS frontend assets, or storefront JavaScript.

For this reason, the current failed-search dashboard should be compatible with Magento stores using Hyvä.

Future features that add storefront behavior tracking, product click tracking, add-to-cart tracking, or search interaction tracking will need to be implemented in a Hyvä-compatible or theme-neutral way.
