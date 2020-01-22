## Wordpress Wrapper Loader

Loading constants and environment variables from .env files.

[![Build Status](https://travis-ci.org/Zippovich2/wordpress-loader.svg?branch=master)](https://travis-ci.org/Zippovich2/wordpress-loader)
[![Packagist](https://img.shields.io/packagist/v/zippovich2/wordpress-loader.svg)](https://packagist.org/packages/zippovich2/wordpress-loader)


### Installation

*Requirements:*

* php ^7.2.5

```sh
$ composer require zippovich2/wordpress-loader
```

### Usage

Add code at beginning in `wp-config.php`:

```php
use WordpressWrapper\Loader\Loader;

//...

$loader = new Loader();
$loader->load();
```

You can specify own paths:

```php
use WordpressWrapper\Loader\Loader;

//...

$projectRoot = '/path/to/project-root/'; // this directory should containt .env files.
$public = $projectRoot . '/public'; // this directory should containt index.php file
$wpCore = '/wp'; // relevant path from $public to wordpress core directory

$loader = new Loader();
$loader->load($wpCore, $projectRoot, $public);
```

If you want to enable debug add one more line:

```php
use WordpressWrapper\Loader\Loader;

//...

$loader = new Loader();
$loader->load();
// default path is '/var/log', relevant ro project root
// it will create directroy if not exists
$loader->debugSettings('/path/to/log/dir');
```
