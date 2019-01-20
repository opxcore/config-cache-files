<?php

namespace OpxCore\Config;

use OpxCore\Interfaces\ConfigCacheRepositoryInterface;

class ConfigCacheFile implements ConfigCacheRepositoryInterface
{
    /**
     * Path to cache file.
     *
     * @var  string
     */
    protected $path;

    /**
     * Filename for cache file.
     *
     * @var  string
     */
    protected $prefix;

    /**
     * Extension for cache file.
     *
     * @var  string
     */
    protected $extension;

    /**
     * ConfigCacheFile constructor.
     *
     * @param  string|null $path
     * @param  string|null $prefix
     * @param  string|null $extension
     */
    public function __construct($path = null, $prefix = 'config.', $extension = '.cache')
    {
        $this->path = $path;
        $this->prefix = $prefix;
        $this->extension = $extension;
    }

    /**
     * Load config from cache.
     *
     * @param  string $id
     * @param  array $config

     * @return  bool
     */
    public function load(&$config, $id = 'default'): bool
    {

    }

    /**
     * Save config to cache.
     *
     * @param  string $id
     * @param  array $config
     *
     * @return  bool
     */
    public function save($config, $id = 'default'): bool
    {

    }
}