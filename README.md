## Installation

1. You can install the package via composer:
``` bash
composer require "aifst/laravel-messages:^1.0.0"
```

2. Optional: The service provider will automatically get registered. Or you may manually add the service provider in your config/app.php file:

``` bash
'providers' => [
    // ...
    Aifst\MessagesServiceProvider::class,
];
```

3. You should publish the migration and the config/messages.php config file with:

``` bash
php artisan vendor:publish --provider="Aifst\MessagesServiceProvider"
```
