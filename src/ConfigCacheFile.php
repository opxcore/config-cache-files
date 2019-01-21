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
     *
     * @return  bool
     */
    public function load(&$config, $id = 'default'): bool
    {
        if (!$this->path) {
            return false;
        }

        $filename = $this->makeFilename($id);

        if (!file_exists($filename)) {
            return false;
        }

        $config = unserialize(file_get_contents($filename), ['allowed_classes' => false]);

        return true;
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
        if (!$this->path || (!is_dir($this->path) && !mkdir($this->path, 0644, true))) {
            return false;
        }

        $filename = $this->makeFilename($id);

        return file_put_contents($filename, serialize($config)) !== false;
    }

    /**
     * Make filename for given id.
     *
     * @param  string $id
     *
     * @return  string
     */
    protected function makeFilename($id): string
    {
        $prefix = $this->prefix ? $this->prefix . '.' : null;
        $extension = $this->extension ? $this->extension . '.' : null;

        return $this->path . DIRECTORY_SEPARATOR . $prefix . $id . $extension;
    }
}