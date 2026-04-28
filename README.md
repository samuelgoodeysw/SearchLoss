# Search Loss Audit for Magento 2

Search Loss Audit is a Magento-native search audit and diagnosis module.

It helps merchants understand where customer search demand is being missed, what that missed demand may be worth, and what Magento should review first.

Search Loss Audit focuses first on failed searches:

> Customer searched -> Magento returned zero results.

The module turns those failed searches into a prioritised audit view with diagnosis, supporting Magento evidence, and recommended review/fix steps.

---

## What Search Loss Audit does

Search Loss Audit helps answer:

- What are customers searching for but not finding?
- Which failed searches happen most often?
- Which missed searches may represent the most estimated demand value?
- Does Magento already contain related products, categories, SKUs, or product attributes?
- Is the likely issue catalogue coverage, product data, SKU/part-number matching, search configuration, product visibility, stock, website assignment, or manual review?
- What should the merchant review first?

The goal is not to replace Magento search or a third-party search platform. The goal is to provide a practical audit layer that shows where search may be leaking demand and what should be investigated first.

---

## Current admin location

```text
Reports -> Business Intelligence -> Search Loss Audit
```

Technical module namespace and path remain unchanged:

```text
Scandiweb_SearchLoss
app/code/Scandiweb/SearchLoss
```

---

## Current features

### Audit snapshot

The audit snapshot gives a quick commercial overview:

- Failed Terms
- Failed Searches
- Est. Demand Value
- AOV
- Search-to-order rate

Est. Demand Value is directional. It is not guaranteed lost revenue.

Current model:

```text
failed search count x average order value x search-to-order rate
```

Use this as a prioritisation signal, not an exact revenue claim.

---

### Top money actions

The Top money actions section groups failed search findings by fix effort bucket.

It helps merchants quickly see which types of work may matter most, such as:

- Attribute/search configuration fix
- Catalogue coverage review
- Catalogue data fix
- Manual review

Cards are clickable and filter the findings table by work type.

---

### Prioritized search findings

The main audit table shows:

- Term
- Searches
- Est. Demand Value
- Priority
- Diagnosis
- Recommended Next Step
- Diagnosis/details cue

Rows are clickable. Expanding a row shows the full diagnosis, recommended fix, and supporting Magento evidence.

---

### Expanded diagnosis rows

Expanded rows are organised as:

1. Diagnosis
2. Recommended fix
3. Evidence

#### Diagnosis

Plain-English explanation of what the failed search likely means.

#### Recommended fix

Includes:

- Magento admin shortcuts
- full suggested fix text
- step-by-step Magento checks/fixes

Current shortcuts include:

- Products
- Categories
- Search terms
- Indexes
- Cache

#### Evidence

Shows the Magento evidence behind the diagnosis, including catalogue, product, stock, website, category, identity attribute, and searchable attribute signals.

---

## Current diagnosis types

Search Loss Audit currently uses rule-based, evidence-led diagnosis. It does not use an LLM to generate recommendations.

Current diagnosis types include:

- Product exists but search is not matching it
- Product exists but is disabled
- Product exists but is not visible in search
- Product exists but is not assigned to website
- Product exists but is not assigned to category
- Product exists but may be out of stock
- SKU or part number is not matching
- Brand or product terms are missing
- Spelling or format variant
- Fitment or use case is unclear
- Product or category may be missing
- Search term is too broad or unclear
- Needs manual review

---

## Magento evidence checked

Search Loss Audit uses Magento-native evidence where available.

Current checks include:

- failed search terms from `search_query`
- failed search popularity/count
- average order value from `sales_order`
- search-to-order rate
- product name matches
- SKU matches
- category name matches
- related keyword product matches
- related keyword category matches
- product enabled/disabled status
- product visibility
- website assignment
- category assignment
- stock status
- searchable product attribute configuration
- identity attributes such as manufacturer, brand, MPN, model, part number, OEM, supplier, or vendor where present

---

## Searchable attribute evidence

Search Loss Audit checks whether important product fields exist and whether they are searchable.

Current evidence includes:

- Core product fields found
- Searchable core product fields
- Non-searchable core product fields
- Core field search coverage
- Identity fields found
- Searchable identity fields
- Non-searchable identity fields
- Identity field search coverage

This helps support diagnoses such as:

> Related products exist, but the relevant fields may not be configured as searchable.

Core product fields checked include:

- name
- SKU
- description
- short description

Identity attribute candidates include:

- manufacturer
- brand
- MPN
- part number
- product code
- model
- OEM
- supplier
- vendor

The module discovers which of these attributes exist in the Magento catalogue instead of hard-coding one client-specific field.

---

## API endpoint

Search Loss Audit exposes dashboard/audit data through a Magento REST endpoint:

```text
/rest/V1/search-loss/dashboard
```

The same backend provider powers both:

- Magento admin dashboard
- REST API response

Main backend provider:

```text
app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php
```

This keeps the admin UI and API output aligned.

---

## Data sources

Current Magento-native data sources include:

```text
search_query
sales_order
catalog_product_entity
catalog_product_entity_varchar
catalog_product_entity_text
catalog_product_entity_int
catalog_product_website
catalog_category_product
catalog_category_entity_varchar
cataloginventory_stock_status
eav_attribute
eav_entity_type
eav_attribute_option
eav_attribute_option_value
catalog_eav_attribute
```

---

## Exports

The admin page currently includes two export actions:

- Download current view
- Download all findings

The export is designed to support audit review and client discussion.

---

## Market positioning

Search Loss Audit is a Magento search audit and diagnosis tool.

It is not:

- a search engine
- an Adobe Live Search replacement
- an Algolia, Klevu, or Searchspring replacement
- a guaranteed lost-revenue calculator
- a generic analytics dashboard
- an AI automation product

Search platforms such as Algolia, Klevu, Adobe Live Search, Searchspring, and similar tools focus on the search experience itself:

- relevance
- autocomplete
- typo tolerance
- merchandising
- ranking
- behavioural analytics
- conversions

Search Loss Audit sits before, beside, or after those tools as a diagnostic layer.

It helps merchants understand:

- what customers searched for but could not find
- whether related catalogue evidence already exists
- whether the problem looks like product data, visibility, stock, SKU matching, attribute searchability, synonyms, or search configuration
- which search problems should be reviewed first
- what directional demand value may be attached to those problems

---

## Recommended commercial framing

Strongest near-term offer:

```text
Fixed Search Loss Audit
```

Suggested audit output:

- top failed search opportunities
- search volume
- estimated demand value
- likely diagnosis
- supporting Magento evidence
- fix effort bucket
- recommended next step
- prioritised fix list
- client review session or written summary

Suggested positioning:

> Search Loss Audit is a low-risk Magento search diagnosis. It uses search data the store already has, checks failed searches against Magento catalogue evidence, estimates directional demand value, and gives a prioritised list of what to review first.

---

## Revenue and demand value wording

Use careful directional language.

Recommended wording:

- Est. Demand Value
- Estimated demand value
- Directional demand value
- Potential search opportunity
- Estimated revenue at risk

Avoid overclaiming:

- guaranteed lost revenue
- exact revenue loss
- proven recovered revenue

Suggested disclaimer:

> Estimated demand value is directional. It highlights where search friction may be blocking product discovery, not guaranteed lost revenue.

---

## Hyva compatibility

The current module should be safe for Hyva-based storefronts because it has no storefront frontend footprint.

The module is currently:

- Magento Admin focused
- REST API focused
- database-read focused

It does not currently depend on:

- Luma storefront templates
- Knockout storefront components
- RequireJS storefront behaviour
- Magento frontend UI components
- checkout frontend code

Future weak-search tracking may require storefront tracking. If added, it should be implemented in a Hyva-compatible or theme-neutral way.

---

## Performance testing note

Local stress testing was completed using a removable `SLTEST` dataset in Magento's `search_query` table.

Test summary:

- 5,000 additional failed-search rows were inserted for stress testing.
- The API response stayed capped to the prioritised findings set rather than returning every failed term.
- The REST endpoint remained fast locally, responding in roughly 0.5 seconds during the 5,000-row test.
- The response size stayed controlled at around 60 KB.
- The stress rows were removed after testing.

This supports the current MVP approach: Search Loss Audit should behave as a prioritised audit view, not a full raw export of every historical failed search.

For larger production catalogues, the recommended approach is still:

- rank failed searches first
- deep-diagnose only the highest-priority findings
- keep endpoint output capped
- avoid returning every raw search row in the dashboard

---

## Current limitations

Search Loss Audit is currently focused on failed searches only.

A failed search means:

```text
Customer searched -> Magento returned zero results
```

It does not yet fully analyse weak searches.

A weak search would mean:

```text
Customer searched -> Magento returned results -> customer did not meaningfully engage
```

Weak-search analysis likely requires GA4, onsite tracking, or search platform analytics because Magento's native `search_query` table does not reliably show product clicks, add-to-cart events, quote requests, or purchases after search.

---

## Roadmap

### Phase 1: Failed Searches

Current phase.

Identifies:

- missing catalogue coverage
- weak product/category naming
- missing synonyms
- SKU or part-number matching issues
- search configuration issues
- visibility/status/website/category/stock issues
- searchable attribute problems

### Phase 2: Weak Searches

Future phase.

Would identify searches that returned results but did not lead to meaningful engagement.

Possible signals:

- low product click-through after search
- low add-to-cart after search
- low quote/request after search
- low purchase conversion after search
- search refinement rate
- search abandonment rate

Likely data sources:

- GA4
- onsite tracking
- search platform analytics

### Phase 3: Search Opportunity Control Center

Possible future broader product:

- Failed Searches
- Weak Searches
- Catalogue Gaps
- Product Data Health
- Search Configuration Issues
- Merchandising Opportunities
- Revenue Recovery Opportunities
- Workflow / assignment / resolution tracking

---

## Package readiness checklist

Before broader installation or marketplace-style packaging, review:

- `composer.json`
- `module.xml` version
- README/customer-facing documentation
- ACL labels
- admin menu labels
- REST API ACL
- no test tokens
- no dev-only wording
- no production-unsafe instructions
- compatibility matrix
- install/upgrade instructions
- no unnecessary storefront assets

---

## Useful validation commands

From Magento root:

```bash
cd /home/magento/magento
```

Check Git state:

```bash
git status
git log --oneline -8
```

Check PHP syntax:

```bash
php -l app/code/Scandiweb/SearchLoss/Model/SearchLossDataProvider.php
php -l app/code/Scandiweb/SearchLoss/Controller/Adminhtml/Export/All.php
php -l app/code/Scandiweb/SearchLoss/view/adminhtml/templates/dashboard.phtml
```

Flush cache:

```bash
bin/magento cache:flush
```

Test REST endpoint:

```bash
curl -s http://localhost/rest/V1/search-loss/dashboard | head -c 1500
echo
```

Check that no removable stress-test rows remain:

```bash
mysql -u root -p -e "
SELECT COUNT(*) AS sltest_rows
FROM magento.search_query
WHERE query_text LIKE 'SLTEST%';
"
```

Expected before demo:

```text
sltest_rows = 0
```

---

## Technical naming note

Visible/admin/commercial labels should use:

```text
Search Loss Audit
```

Technical names should remain stable for now:

```text
Scandiweb_SearchLoss
app/code/Scandiweb/SearchLoss
/rest/V1/search-loss/dashboard
```

Do not perform a full namespace/module rename unless packaging or distribution requires it later.
