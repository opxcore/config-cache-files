# config-cache-files

[![Build Status](https://travis-ci.com/opxcore/config-cache-files.svg?branch=main)](https://travis-ci.com/opxcore/config-cache-files)
[![Coverage Status](https://coveralls.io/repos/github/opxcore/config-cache-files/badge.svg)](https://coveralls.io/github/opxcore/config-cache-files)
[![Latest Stable Version](https://poser.pugx.org/opxcore/config-cache-files/v/stable)](https://packagist.org/packages/opxcore/config-cache-files)
[![Total Downloads](https://poser.pugx.org/opxcore/config-cache-files/downloads)](https://packagist.org/packages/opxcore/config-cache-files)
[![License](https://poser.pugx.org/opxcore/config-cache-files/license)](https://packagist.org/packages/opxcore/config-cache-files)

## Installing

```shell
composer require opxcore/config-cache-files
```

### Standalone usage:

```php
use OpxCore\Config\ConfigCacheFiles;

$configFiles = new ConfigCacheFiles($path);
```

### Usage with [container](https://github.com/opxcore/container)

```php
use OpxCore\Config\Interfaces\ConfigCacheInterface;
use OpxCore\Config\ConfigCacheFiles;

$container->bind(
    ConfigCacheInterface::class, 
    ConfigCacheFiles::class, 
    ['path' => $path]
);

$configCache = $container->make(ConfigCacheInterface::class);

// or

$container->bind(ConfigCacheInterface::class, ConfigCacheFiles::class);

$configCache = $container->make(ConfigCacheInterface::class, ['path' => $path]);
```

Where `$path` is absolute path to folder with configuration cache files.

## Loading config cache

```php
$loaded = $configCache->load($config, $profile)
```

Loads array of configurations from path given in a constructor. If `$profile` is not set driver will search file with
name `config.cache`, in other case name will be `config.given_profile.cache`. If file exists and not expired (this
option stored inside file) array of configuration will be loaded to `$config` variable and `true` would be returned as
function return value. In all other cases function returns `false` and `$config` variable would be not affected.

## Saving config cache

```php
$configCache->save($config, $profile, $ttl)
```

Saves `$config` array lo file (see loading). `$ttl` is time to live for cached data in seconds.
