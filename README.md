# magento2-module-freightbase

## Overview:

Boilerplate for shipping integrations.

Generic base fields added:
  freight_class,
  must_ship_freight,
  declared_value,
  freight_length,
  freight_width,
  freight_height
  
Reference internal 'EXAMPLE CARRIER' directory for how to build out an api integration off of the base module. This is a fully working integration with R&L Freight Shipping.

## Installation

### Composer

```bash
$ composer config repositories.augustash-freightbase vcs https://github.com/augustash/magento2-module-freightbase.git
$ composer require augustash/module-freightbase:~1.0.0
