# SearchLoss
M2 2.4.8 p4 - SearchLoss Module
# Magento Search Loss Dashboard

## Overview

This module provides an admin-only dashboard to identify search-related revenue loss in Magento.

It highlights poor search performance and missed opportunities by analyzing search queries and user behavior.

## Goals

* Identify searches with:

  * Zero results
  * No clicks
  * No add-to-cart
  * No purchases
* Highlight high-volume failing search terms
* Estimate potential lost revenue

## Current Status

🚧 MVP in progress

Current version includes:

* Admin dashboard page
* Magento `search_query` data analysis
* Basic table of search terms and performance

Planned:

* GA4 integration
* Revenue estimation
* Cron-based data sync
* Advanced UI grids and filters

## Installation

1. Place module in:

   ```
   app/code/Scandiweb/SearchLoss
   ```

2. Run:

   ```
   bin/magento setup:upgrade
   bin/magento cache:flush
   ```

## Usage

* Navigate to:

  ```
  Admin → Reports → Search Loss
  ```

## Data Sources (Planned)

* Magento:

  * `search_query`
  * `sales_order`
  * `quote`
* Google Analytics 4:

  * Search events
  * Product views
  * Conversions

## Roadmap

* [ ] Magento-only MVP dashboard
* [ ] GA4 API integration
* [ ] Lost revenue calculation
* [ ] Filtering by date/store
* [ ] UI component grids
* [ ] Export/reporting

## Notes

This module is intended for internal use and experimentation.

## Author

Scandiweb
