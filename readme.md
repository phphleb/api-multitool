A set of useful traits for API implementation
=====================

![PHP](https://img.shields.io/badge/PHP-^7.4-blue) ![PHP](https://img.shields.io/badge/PHP-8-blue) [![License: MIT](https://img.shields.io/badge/License-MIT%20(Free)-brightgreen.svg)](https://github.com/phphleb/hleb/blob/master/LICENSE)

Can be used either separately or in conjunction with the HLEB2 framework: [github.com/phphleb/hleb](https://github.com/phphleb/hleb) 

The set represents independent traits, each of which implements highly specialized methods that are used in controllers.

### Installation

Using Composer
 ```bash
  composer require phphleb/api-multitool
 ```
or download the library to the 'vendor/phphleb/api-multitool' folder.

 ### BaseApiTrait

All auxiliary traits are collected in the trait **BaseApiTrait** as a set. Therefore, it is enough to connect it to the controller and get a complete implementation.
If another set of these traits is needed, then you need to either use them as a group or combine them into your own set.
It is best to include such a trait in the parent class of controllers that implement the API, so as not to have to do this for everyone.

For the HLEB2 framework it might look like this (implementation example):

```php
<?php
// File /app/Controllers/Api/BaseApiActions.php

namespace App\Controllers\Api;

use Hleb\Base\Controller;
use Phphleb\ApiMultitool\BaseApiTrait;

class BaseApiActions extends Controller
{
    // Adding a set of traits for the API.
    use BaseApiTrait;

    function __construct(array $config = [])
    {
        parent::__construct($config);

        // Passing the debug mode value to the API constructor.
        $this->setApiBoxDebug($this->container->settings()->isDebug());
    }
}

```
After this, all controllers inheriting from this class will have methods from each trait in the set.

More details can be found in the [**framework documentation**](https://hleb2framework.ru).

### Testing

API traits tested using [github.com/phphleb/api-tests](https://github.com/phphleb/api-tests)
