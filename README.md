# BeastBytes Token DB
PHP file storage for the [BeastBytes Token](https://github.com/beastbytes/token.git) package.

Do not use this package directly;
use TokenManager in [BeastBytes Token](https://github.com/beastbytes/token.git) package.

## Requirements
* PHP 8.1 or higher.

## Installation
Installed the package with Composer:
```php
composer require beastbytes/token-php
```
or add the following to the 'require' section composer.json:
```json
"beastbytes/token-db": "<reqirement-constraint>"
```

## Configuration
If using Yii's dependency injection container, add the following to the "params" of your configuration:
```php
return [
    'beastbytes/token' => [
        'tableName' => 'token_table_name', // use TokenStorage::TABLE_NAME 
    ],
    // other parameters
];
```
