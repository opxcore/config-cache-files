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
    protected $prefix = 'config';

    /**
     * Extension for cache file.
     *
     * @var  string
     */
    protected $extension = 'cache';

    /**
     * ConfigCacheFile constructor.
     *
     * @param  string|null $path
     */
    public function __construct($path = null)
    {
        $this->path = $path;
    }

    /**
     * Load config from cache.
     *
     * @param  array $config
     * @param  string|null $profile

     * @return  bool
     */
    public function load(&$config, $profile = null): bool
    {
        if (!$this->path) {
            return false;
        }

        $filename = $this->makeFilename($profile);

        if (!file_exists($filename)) {
            return false;
        }

        $content = file_get_contents($filename);

        if($content !== false) {
            $config = unserialize($content, ['allowed_classes' => false]);
        }

        return $content !== false;
    }

    /**
     * Save config to cache.
     *
     * @param  array $config
     * @param  string|null $profile
     *
     * @return  bool
     */
    public function save($config, $profile = null): bool
    {
        $dirIsSet = $this->path !== null;

        if ($dirIsSet && !is_dir($this->path)) {
            // fix for mkdir race condition
            $dirIsSet = !is_dir($this->path) && mkdir($this->path, 0644, true) && is_dir($this->path);
        }

        if($dirIsSet) {
            $filename = $this->makeFilename($profile);
            $saved = file_put_contents($filename, serialize($config)) !== false;
        }

        return  $dirIsSet && isset($saved);
    }

    /**
     * Make filename for given id.
     *
     * @param  string $profile
     *
     * @return  string
     */
    protected function makeFilename($profile): string
    {
        $filename = trim(trim($this->prefix . '.' . $profile, '.') . '.' . $this->extension, '.');

        return $this->path . DIRECTORY_SEPARATOR . $filename;
    }
}