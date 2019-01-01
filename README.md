# PhrantiK/helpscout-invoiceninja 1.1

Dynamic app for [Help Scout](http://helpscout.net) that fetches invoices from InvoiceNinja.

Finds all invoices associated with the customer email(s).

## Install

1. Clone into a folder from your InvoiceNinja public folder:

```
git clone https://github.com/PhrantiK/helpscout-invoiceninja.git choosefoldername
```

2. Run: `composer update` to install dependencies
3. Generate random secret key: `date | md5sum`
4. Add secret key into InvoiceNinja .env file: `HS_SECRET=YOURSECRETKEY`
5. Change locale if needed (note: only tested with en-US)
6. Set up the custom app in Help Scout with your URL & Secret key

## todo

- [ ] Create customer summary
- [x] Update to use InvoiceNinja .env file
