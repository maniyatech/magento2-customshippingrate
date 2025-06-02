# ManiyaTech CustomShippingRate module for Magento 2

ManiyaTech CustomShippingRate is a powerful Magento 2 extension that lets you add and manage custom flat-rate shipping methods for both admin orders and customer checkout. Simplify your shipping strategy, control costs, and offer a better experience to your buyers.

Whether you're shipping via USPS, UPS, FedEx, DHL, or any other carrier, this extension gives you full control over your shipping rates—no need to rely solely on predefined methods or complex table rates.

## Key Features

<ul>
  <li><strong>Admin Side Support:</strong> Easily apply shipping rates when creating orders in the admin panel.</li>
  <li><strong>Frontend Checkout:</strong> Enable or disable shipping method visibility on the storefront checkout page.</li>
  <li><strong>Country Restrictions:</strong> Limit shipping availability to specific countries.</li>
  <li><strong>Custom Shipping Types:</strong> Define delivery types like standard, express, pickup, etc.</li>
  <li><strong>Error Messaging:</strong> Show custom messages when shipping is not available.</li>
  <li><strong>Sort Order Configuration:</strong> Set the display order of this method among others.</li>
</ul>

## How to install ManiyaTech_CustomShippingRate module

### Composer Installation

Run the following command in Magento 2 root directory to install ManiyaTech_CustomShippingRate module via composer.

#### Install

```
composer require maniyatech/magento2-customshippingrate
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```

#### Update

```
composer update maniyatech/magento2-customshippingrate
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```

Run below command if your store is in the production mode:

```
php bin/magento setup:di:compile
```

### Manual Installation

If you prefer to install this module manually, kindly follow the steps described below - 

- Download the latest version [here](https://github.com/maniyatech/magento2-customshippingrate/archive/refs/heads/main.zip) 
- Create a folder path like this `app/code/ManiyaTech/CustomShippingRate` and extract the `main.zip` file into it.
- Navigate to Magento root directory and execute the below commands.

```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```
