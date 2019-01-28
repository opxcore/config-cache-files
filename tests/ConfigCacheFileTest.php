<?php

use PHPUnit\Framework\TestCase;

class ConfigCacheFileTest extends TestCase
{
    protected $path;
    protected $temp;

    public function setUp()
    {
        $this->path = __DIR__ . DIRECTORY_SEPARATOR . 'cache';
        $this->temp = sys_get_temp_dir();
    }

    public function test_Wrong_Path(): void
    {
        $conf = new \OpxCore\Config\ConfigCacheFile($this->path . 'wrong');

        $config = [];
        $loaded = $conf->load($config);
        $this->assertFalse($loaded);
        $this->assertEquals([], $config);
    }

    public function test_Null_Path(): void
    {
        $conf = new \OpxCore\Config\ConfigCacheFile(null);
        $config = [];
        $loaded = $conf->load($config);
        $this->assertFalse($loaded);
        $this->assertEquals([], $config);
    }

    public function test_File_Not_Exists(): void
    {
        $conf = new \OpxCore\Config\ConfigCacheFile(__DIR__);
        $config = [];
        $loaded = $conf->load($config, 'empty');
        $this->assertFalse($loaded);
        $this->assertEquals([], $config);
    }

    public function test_Save(): void
    {
        $path = $this->temp . DIRECTORY_SEPARATOR . 'test';
        $conf = new \OpxCore\Config\ConfigCacheFile($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config);
        $this->assertTrue($saved);
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'config.cache');
        $this->assertFileIsReadable($path . DIRECTORY_SEPARATOR . 'config.cache');
        $this->assertStringEqualsFile($path . DIRECTORY_SEPARATOR . 'config.cache', 'a:2:{s:7:"expires";N;s:6:"config";a:1:{s:4:"test";s:2:"ok";}}');
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
        rmdir($path);
    }

    public function test_Save_Profile(): void
    {
        $path = $this->temp . DIRECTORY_SEPARATOR . 'test';
        $conf = new \OpxCore\Config\ConfigCacheFile($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config, 'profile');
        $this->assertTrue($saved);
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'config.profile.cache');
        $this->assertFileIsReadable($path . DIRECTORY_SEPARATOR . 'config.profile.cache');
        $this->assertStringEqualsFile($path . DIRECTORY_SEPARATOR . 'config.profile.cache', 'a:2:{s:7:"expires";N;s:6:"config";a:1:{s:4:"test";s:2:"ok";}}');
        unlink($path . DIRECTORY_SEPARATOR . 'config.profile.cache');
        rmdir($path);
    }

    public function test_Save_No_Folder(): void
    {
        $path = $this->temp . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'test';
        $conf = new \OpxCore\Config\ConfigCacheFile($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config);
        $this->assertTrue($saved);
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
        rmdir($path);
        rmdir($this->temp . DIRECTORY_SEPARATOR . 'test');
    }

    public function test_Load(): void
    {
        $conf = new \OpxCore\Config\ConfigCacheFile($this->path);
        $config = [];
        $loaded = $conf->load($config);
        $this->assertTrue($loaded);
        $this->assertEquals(['test' => 'ok'], $config);
    }

    public function test_Load_Profile(): void
    {
        $conf = new \OpxCore\Config\ConfigCacheFile($this->path);
        $config = [];
        $loaded = $conf->load($config, 'profile');
        $this->assertTrue($loaded);
        $this->assertEquals(['test' => 'ok'], $config);
    }

    public function test_Ttl_Ok(): void
    {
        $path = $this->temp . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'test';
        $conf = new \OpxCore\Config\ConfigCacheFile($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config, null, 100);
        $this->assertTrue($saved);
        $loaded = $conf->load($config);
        $this->assertTrue($loaded);
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
        rmdir($path);
        rmdir($this->temp . DIRECTORY_SEPARATOR . 'test');
    }

    public function test_Ttl_Not_Ok(): void
    {
        $path = $this->temp . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'test';
        $conf = new \OpxCore\Config\ConfigCacheFile($path);
        $config = ['test' => 'ok'];
        $saved = $conf->save($config, null, 1);
        $this->assertTrue($saved);
        sleep(2);
        $loaded = $conf->load($config);
        $this->assertFalse($loaded);
        unlink($path . DIRECTORY_SEPARATOR . 'config.cache');
        rmdir($path);
        rmdir($this->temp . DIRECTORY_SEPARATOR . 'test');
    }
}
