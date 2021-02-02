<?php
/*
 * This file is part of the OpxCore.
 *
 * Copyright (c) Lozovoy Vyacheslav <opxcore@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpxCore\Config;

use OpxCore\Config\Interfaces\ConfigCacheInterface;

class ConfigCacheFiles implements ConfigCacheInterface
{
    /**
     * Path to cache file.
     *
     * @var  string|null
     */
    protected ?string $path;

    /**
     * Filename for cache file.
     *
     * @var  string
     */
    protected string $prefix = 'config';

    /**
     * Extension for cache file.
     *
     * @var  string
     */
    protected string $extension = 'cache';

    /**
     * ConfigCacheFile constructor.
     *
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }

    /**
     * Load config from cache.
     *
     * @param array $config
     * @param string|null $profile
     *
     * @return  bool
     */
    public function load(array &$config, $profile = null): bool
    {
        if (!$this->path) {
            return false;
        }

        $filename = $this->makeFilename($profile);

        if (!file_exists($filename)) {
            return false;
        }

        $content = file_get_contents($filename);

        if ($content !== false) {
            $restored = unserialize($content, ['allowed_classes' => false]);

            $expiresAt = (isset($restored['expires']) || array_key_exists('expires', $restored)) ? $restored['expires'] : 0;

            if ($this->isExpired($expiresAt)) {
                return false;
            }

            $config = $restored['config'] ?? [];
        }

        return $content !== false;
    }

    /**
     * Make filename for given id.
     *
     * @param string|null $profile
     *
     * @return  string
     */
    protected function makeFilename(?string $profile): string
    {
        $filename = trim(trim($this->prefix . '.' . $profile, '.') . '.' . $this->extension, '.');

        return $this->path . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Save config to cache.
     *
     * @param array $config
     * @param string|null $profile
     * @param integer|null $ttl Time in seconds to cache lives, null for infinity.
     *
     * @return  bool
     */
    public function save(array $config, $profile = null, $ttl = null): bool
    {
        $dirIsSet = $this->path !== null;

        if ($dirIsSet && !is_dir($this->path)) {
            // fix for mkdir race condition
            $dirIsSet = !is_dir($this->path) && mkdir($this->path, 0755, true) && is_dir($this->path);
        }

        if ($dirIsSet) {
            $filename = $this->makeFilename($profile);
            $toSave = [
                'expires' => $ttl ? time() + $ttl : null,
                'config' => $config,
            ];
            $saved = file_put_contents($filename, serialize($toSave)) !== false;
        }

        return $dirIsSet && isset($saved);
    }

    /**
     * Check timestamp was not expired.
     *
     * @param integer|null $timestamp
     *
     * @return  bool
     */
    protected function isExpired(?int $timestamp): bool
    {
        return ($timestamp !== null) && ($timestamp < time());
    }
}