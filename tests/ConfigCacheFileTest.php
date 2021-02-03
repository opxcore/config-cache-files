<?php
/*
 * This file is part of the OpxCore.
 *
 * Copyright (c) Lozovoy Vyacheslav <opxcore@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use OpxCore\Config\ConfigCacheFiles;
use OpxCore\Config\Exceptions\ConfigCacheException;
use PHPUnit\Framework\TestCase;

class ConfigCacheFileTest extends TestCase
{
    protected string $path;
    protected string $temp;

    protected function setUp(): void
    {
        $this->path = __DIR__ . DIRECTORY_SEPARATOR . 'cache';
        $this->temp = sys_get_temp_dir();
    }

    public function test_Wrong_Path(): void
    {
        $cache = new ConfigCacheFiles($this->path . 'wrong');

        $config = [];
        $loaded = $cache->load($config);
        self::assertFalse($loaded);
        self::assertEquals([], $config);
    }

    public function test_Null_Path(): void
    {
        $cache = new ConfigCacheFiles(null);
        $config = [];
        $loaded = $cache->load($config);
        self::assertFalse($loaded);
        self::assertEquals([], $config);
    }

    public function test_File_Not_Exists(): void
    {
        $cache = new ConfigCacheFiles(__DIR__);
        $config = [];
        $loaded = $cache->load($config, 'empty');
        self::assertFalse($loaded);
        self::assertEquals([], $config);
    }

    public function test_Save(): void
    {
        $path = $this->temp;
        $cache = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $cache->save($config);
        self::assertTrue($saved);
        self::assertFileExists($path . DIRECTORY_SEPARATOR . 'config.cache');
        self::assertFileIsReadable($path . DIRECTORY_SEPARATOR . 'config.cache');
        self::assertStringEqualsFile($path . DIRECTORY_SEPARATOR . 'config.cache', 'a:2:{s:7:"expires";N;s:6:"config";a:1:{s:4:"test";s:2:"ok";}}');
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
    }

    public function test_Save_Profile(): void
    {
        $path = $this->temp . DIRECTORY_SEPARATOR . 'cache';
        $cache = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $cache->save($config, 'profile');
        self::assertTrue($saved);
        self::assertFileExists($path . DIRECTORY_SEPARATOR . 'config.profile.cache');
        self::assertFileIsReadable($path . DIRECTORY_SEPARATOR . 'config.profile.cache');
        self::assertStringEqualsFile($path . DIRECTORY_SEPARATOR . 'config.profile.cache', 'a:2:{s:7:"expires";N;s:6:"config";a:1:{s:4:"test";s:2:"ok";}}');
        unlink($path . DIRECTORY_SEPARATOR . 'config.profile.cache');
        rmdir($path);
    }

    public function test_Save_No_Folder(): void
    {
        $path = $this->temp;
        $cache = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $cache->save($config);
        self::assertTrue($saved);
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
    }

    public function test_Load(): void
    {
        $cache = new ConfigCacheFiles($this->path);
        $config = [];
        $loaded = $cache->load($config);
        self::assertTrue($loaded);
        self::assertEquals(['test' => 'ok'], $config);
    }

    public function test_Load_Profile(): void
    {
        $cache = new ConfigCacheFiles($this->path);
        $config = [];
        $loaded = $cache->load($config, 'profile');
        self::assertTrue($loaded);
        self::assertEquals(['test' => 'ok'], $config);
    }

    public function test_Ttl_Ok(): void
    {
        $path = $this->temp;
        $cache = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $cache->save($config, null, 100);
        self::assertTrue($saved);
        $loaded = $cache->load($config);
        self::assertTrue($loaded);
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
    }

    public function test_Ttl_Not_Ok(): void
    {
        $path = $this->temp;
        $cache = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $cache->save($config, null, 1);
        self::assertTrue($saved);
        sleep(2);
        $loaded = $cache->load($config);
        self::assertFalse($loaded);
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
    }

    public function testLoadBad(): void
    {
        $cache = new ConfigCacheFiles($this->path);
        $config = [];
        $this->expectException(ConfigCacheException::class);
        $cache->load($config, 'bad');
    }

    public function testLoadBadTtl(): void
    {
        $cache = new ConfigCacheFiles($this->path);
        $config = [];
        $this->expectException(ConfigCacheException::class);
        $cache->load($config, 'badttl');
    }

    public function testLoadError(): void
    {
        copy(
            $this->path . DIRECTORY_SEPARATOR . 'config.cache',
            $this->temp . DIRECTORY_SEPARATOR . 'config.cache'
        );
        chmod($this->temp . DIRECTORY_SEPARATOR . 'config.cache', 0333);
        $cache = new ConfigCacheFiles($this->temp);
        $config = [];
        $exceptionClass = null;
        try {
            $cache->load($config);
        } catch (Error | Exception $e) {
            $exceptionClass = get_class($e);
        }
        self::assertEquals(ConfigCacheException::class, $exceptionClass);
        unlink($this->temp . DIRECTORY_SEPARATOR . 'config.cache');
    }

    public function testSaveDirError(): void
    {
        if (!is_dir($this->temp . DIRECTORY_SEPARATOR . 'cache_test')) {
            mkdir($this->temp . DIRECTORY_SEPARATOR . 'cache_test');
        }
        chmod($this->temp . DIRECTORY_SEPARATOR . 'cache_test', 0444);

        $cache = new ConfigCacheFiles($this->temp . DIRECTORY_SEPARATOR . 'cache_test' . DIRECTORY_SEPARATOR . 'test');
        $config = [];
        $cache->load($config);

        $exceptionClass = null;
        try {
            $cache->save($config);
        } catch (Error | Exception $e) {
            $exceptionClass = get_class($e);
        }
        self::assertEquals(ConfigCacheException::class, $exceptionClass);
        rmdir($this->temp . DIRECTORY_SEPARATOR . 'cache_test');
    }

    public function testSaveError(): void
    {
        $tempCache = $this->temp . DIRECTORY_SEPARATOR . 'config.cache';

        if (file_exists($tempCache)) {
            unlink($tempCache);
        }
        copy($this->path . DIRECTORY_SEPARATOR . 'config.cache', $tempCache);
        chmod($this->temp . DIRECTORY_SEPARATOR . 'config.cache', 0444);

        $cache = new ConfigCacheFiles($this->temp);
        $config = [];

        $cache->load($config);

        $exceptionClass = null;
        try {
            $cache->save($config);
        } catch (Error | Exception $e) {
            $exceptionClass = get_class($e);
        }
        self::assertEquals(ConfigCacheException::class, $exceptionClass);
        chmod($tempCache, 0777);
        unlink($tempCache);
    }
}
