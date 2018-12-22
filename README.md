# PhrantiK/helpscout-invoiceninja

Dynamic app for [Help Scout](http://helpscout.net) that fetches invoices from InvoiceNinja.

Finds all invoices associated with the customer email(s).

## Install

1. Git clone or download onto your webhost
2. Run: `composer install` to install dependencies
3. Generate random secret key: `date | md5sum`
4. Put secret key in index.php
5. Put InvoiceNinja database credentials in index.php
6. Change locale if needed (note: only tested with en-US)
6. Change domain to your domain
7. Set up a custom app in Help Scout

## todo

- [ ] Create customer summary
- [ ] Update to use InvoiceNinja .env file
