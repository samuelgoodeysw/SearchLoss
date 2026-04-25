# Magento Search Loss Module

Simple Magento 2 admin dashboard showing lost revenue from poor search.

## What it shows

- Failed searches (0 results)
- Weak searches (low-quality results)
- Estimated lost revenue
- Basic metrics (AOV, conversion rate)
- Date filters (All / 7 days / 30 days)

## How it works

Uses Magento data:

- search_query (search terms)
- sales_order (revenue)

Estimated lost revenue:

search count × conversion rate × average order value

## Install

Copy module:

app/code/Scandiweb/SearchLoss

Run:

bin/magento setup:upgrade
bin/magento cache:flush

## Notes

- Uses mock/demo data
- UI is basic (to be improved)

