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
        $conf = new ConfigCacheFiles($this->path . 'wrong');

        $config = [];
        $loaded = $conf->load($config);
        self::assertFalse($loaded);
        self::assertEquals([], $config);
    }

    public function test_Null_Path(): void
    {
        $conf = new ConfigCacheFiles(null);
        $config = [];
        $loaded = $conf->load($config);
        self::assertFalse($loaded);
        self::assertEquals([], $config);
    }

    public function test_File_Not_Exists(): void
    {
        $conf = new ConfigCacheFiles(__DIR__);
        $config = [];
        $loaded = $conf->load($config, 'empty');
        self::assertFalse($loaded);
        self::assertEquals([], $config);
    }

    public function test_Save(): void
    {
        $path = $this->temp;
        $conf = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config);
        self::assertTrue($saved);
        self::assertFileExists($path . DIRECTORY_SEPARATOR . 'config.cache');
        self::assertFileIsReadable($path . DIRECTORY_SEPARATOR . 'config.cache');
        self::assertStringEqualsFile($path . DIRECTORY_SEPARATOR . 'config.cache', 'a:2:{s:7:"expires";N;s:6:"config";a:1:{s:4:"test";s:2:"ok";}}');
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
    }

    public function test_Save_Profile(): void
    {
        $path = $this->temp . DIRECTORY_SEPARATOR . 'cache';
        $conf = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config, 'profile');
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
        $conf = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config);
        self::assertTrue($saved);
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
    }

    public function test_Load(): void
    {
        $conf = new ConfigCacheFiles($this->path);
        $config = [];
        $loaded = $conf->load($config);
        self::assertTrue($loaded);
        self::assertEquals(['test' => 'ok'], $config);
    }

    public function test_Load_Profile(): void
    {
        $conf = new ConfigCacheFiles($this->path);
        $config = [];
        $loaded = $conf->load($config, 'profile');
        self::assertTrue($loaded);
        self::assertEquals(['test' => 'ok'], $config);
    }

    public function test_Ttl_Ok(): void
    {
        $path = $this->temp;
        $conf = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config, null, 100);
        self::assertTrue($saved);
        $loaded = $conf->load($config);
        self::assertTrue($loaded);
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
    }

    public function test_Ttl_Not_Ok(): void
    {
        $path = $this->temp;
        $conf = new ConfigCacheFiles($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config, null, 1);
        self::assertTrue($saved);
        sleep(2);
        $loaded = $conf->load($config);
        self::assertFalse($loaded);
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
    }
}
