<?php
namespace hconfigure\tests\units;
use hconfigure\tests\TestCase;
use hehe\core\hconfigure\parser\IniParser;

class ExampleTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->config['cachefile'])) {
            //unlink($this->config['cachefile']);
        }
    }

    protected function getConfigFilepath(string $filename)
    {
        return dirname(__DIR__) . '/common/' . $filename;
    }



    public function testPhp()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('user.php'))->load();
        $config = $this->hconfig->toArray();

        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($this->hconfig->has('app_name'));
        $this->assertTrue($this->hconfig->get('app_name') === 'hehe');
        $this->assertTrue($this->hconfig->get('app_locale') === 'zh-CN');
        $this->assertTrue($this->hconfig->get('oss.endpoint') === 'http://localhost');
        $this->assertTrue($this->hconfig->get('oss.admin.policy') === 'admin');

        $this->assertTrue($this->hconfig->has('oss.endpoint'));
        $this->assertTrue($this->hconfig->has('oss.admin.policy'));
        $this->assertTrue(!$this->hconfig->has('ossx'));
        $this->assertTrue(!$this->hconfig->has('oss.endpointx'));
        $this->assertTrue(!$this->hconfig->has('oss.admin.policyx'));
        $this->assertTrue(!$this->hconfig->has('oss.adminx.policy'));
    }

    public function testIni()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('admin.ini'))->load();
        $fileConfig = $this->hconfig->toArray();

        $this->assertTrue($fileConfig['username'] === 'admin');
        $this->assertTrue($fileConfig['email'] === 'admin@admin.com');

        $config = $this->hconfig->toArray();
        $this->assertTrue($config['username'] === 'admin');
        $this->assertTrue($config['email'] === 'admin@admin.com');

        $this->assertTrue($this->hconfig->has('username'));
        $this->assertTrue(!$this->hconfig->has('usernamex'));
    }

    public function testJson()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('config.json'))->load();
        $fileConfig = $this->hconfig->toArray();

        $this->assertTrue($fileConfig['name'] === 'hehe');
        $this->assertTrue($fileConfig['age'] === 18);

        $config = $this->hconfig->toArray();
        $this->assertTrue($config['name'] === 'hehe');
        $this->assertTrue($config['age'] === 18);

        $this->assertTrue($this->hconfig->has('name'));
        $this->assertTrue(!$this->hconfig->has('namex'));
    }

    public function testYaml()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('app.yaml'))->load();
        $fileConfig = $this->hconfig->toArray();

        $this->assertTrue($fileConfig['runtime'] === 'go');
        $this->assertTrue($fileConfig['api_version'] === 'go1');

        $config = $this->hconfig->toArray();
        $this->assertTrue($config['runtime'] === 'go');
        $this->assertTrue($config['api_version'] === 'go1');

        $this->assertTrue($config['handlers']['login'] === 'required');

        $this->assertTrue($this->hconfig->has('runtime'));
        $this->assertTrue(!$this->hconfig->has('runtimex'));
    }

    public function testXml()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('wechat.xml'))->load();
        $fileConfig = $this->hconfig->toArray();

        $this->assertTrue($fileConfig['username'] === 'hehex');
        $this->assertTrue($fileConfig['password'] === '123456');

        $config = $this->hconfig->toArray();
        $this->assertTrue($config['username'] === 'hehex');
        $this->assertTrue($config['password'] === '123456');

        $this->assertTrue($this->hconfig->get('admin.name') === 'admin');
        $this->assertTrue($this->hconfig->get('admin.role') === '超级管理员');

        $this->assertTrue($this->hconfig->has('username'));
        $this->assertTrue(!$this->hconfig->has('usernamex'));
    }

    public function testMoreFile()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('wechat.xml'));
        $this->hconfig->addFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->addFiles($this->getConfigFilepath('admin.ini'))->load();

        $config = $this->hconfig->toArray();
        // php
        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($config['username'] === 'hehex');

        // ini
        $this->assertTrue($config['enabled'] === '1');

        // xml
        $this->assertTrue($this->hconfig->get('admin.name') === 'admin');
        $this->assertTrue($this->hconfig->get('admin.role') === '超级管理员');
    }

    public function testCacheFile()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('wechat.xml'));
        $this->hconfig->addFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->addFiles($this->getConfigFilepath('admin.ini'));
        $this->hconfig->setCacheFile($this->config['cachefile']);
        $this->hconfig->load();

        $config = $this->hconfig->toArray();
        // php
        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($config['username'] === 'hehex');

        // ini
        $this->assertTrue($config['enabled'] === '1');

        // xml
        $this->assertTrue($this->hconfig->get('admin.name') === 'admin');
        $this->assertTrue($this->hconfig->get('admin.role') === '超级管理员');

//
//        // 验证缓存文件与配置文件是否一致
//        $config = $this->hconfig->getRawConfigFromFile();
//
//        $this->assertTrue(json_encode($config) === json_encode(require($this->config['cachefile'])));
    }

    public function testCacheFile1()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('wechat.xml'));
        $this->hconfig->addFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->addFiles($this->getConfigFilepath('admin.ini'));
        $this->hconfig->setCacheFile($this->config['cachefile']);
        $this->hconfig->load();

        $config = $this->hconfig->toArray();
        // php
        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($config['username'] === 'hehex');

        // ini
        $this->assertTrue($config['enabled'] === '1');

        // xml
        $this->assertTrue($this->hconfig->get('admin.name') === 'admin');
        $this->assertTrue($this->hconfig->get('admin.role') === '超级管理员');


        // 验证缓存文件与配置文件是否一致
        $cacheConfig = $this->hconfig->toArray();

        // php
        $this->assertTrue($cacheConfig['app_name'] === 'hehe');
        $this->assertTrue($cacheConfig['username'] === 'hehex');

        // ini
        $this->assertTrue($cacheConfig['enabled'] === '1');

    }

    public function testCacheFile2()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('wechat.xml'));
        $this->hconfig->addFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->addFiles($this->getConfigFilepath('admin.ini'));
        $this->hconfig->addFiles($this->getConfigFilepath('config.php'));
        $this->hconfig->setCacheFile($this->config['cachefile'])->load();

        $config = $this->hconfig->toArray();
        // php
        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($config['username'] === 'hehex');

        // ini
        $this->assertTrue($config['enabled'] === '1');

        // xml
        $this->assertTrue($this->hconfig->get('admin.name') === 'admin');
        $this->assertTrue($this->hconfig->get('admin.role') === '超级管理员');


        // 验证缓存文件与配置文件是否一致
        $cacheConfig = $this->hconfig->toArray();

        // php
        $this->assertTrue($cacheConfig['app_name'] === 'hehe');
        $this->assertTrue($cacheConfig['username'] === 'hehex');

        // ini
        $this->assertTrue($cacheConfig['enabled'] === '1');

    }

    public function testParser()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('admin.ini'));
        $this->hconfig->addParser('ini',[IniParser::class,'parse'])->load();

        $config = $this->hconfig->toArray();
        $this->assertTrue($config['username'] === 'admin');
        $this->assertTrue($config['email'] === 'admin@admin.com');
        $this->assertTrue($this->hconfig->has('username'));
        $this->assertTrue(!$this->hconfig->has('usernamex'));

    }

    public function testParser1()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('admin.ini'));
        $this->hconfig->addParser('ini',function($file){
            return parse_ini_string(file_get_contents($file), true);
        })->load();

        $config = $this->hconfig->toArray();
        $this->assertTrue($config['username'] === 'admin');
        $this->assertTrue($config['email'] === 'admin@admin.com');
        $this->assertTrue($this->hconfig->has('username'));
        $this->assertTrue(!$this->hconfig->has('usernamex'));

    }

    public function testConfigItem()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->addFiles([$this->getConfigFilepath('role.php'),'role']);
        $this->hconfig->load();

        $config = $this->hconfig->toArray();
        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($config['role']['name'] === '角色');
        $this->assertTrue($config['role']['sale']['name'] === '销售');
    }

    public function testAdddir()
    {
        $this->hconfig->addDir($this->getConfigFilepath(''));
        $this->hconfig->load();

        $config = $this->hconfig->toArray();
        $this->assertTrue($config['username'] === 'admin');
        $this->assertTrue($config['email'] === 'admin@admin.com');

        $this->assertTrue($config['runtime'] === 'go');
        $this->assertTrue($config['api_version'] === 'go1');

        $this->assertTrue($config['name'] === 'hehe');
        $this->assertTrue($config['age'] === 18);

        $this->assertTrue($config['app_name'] === 'hehe');

        $this->assertTrue($this->hconfig->get('admin.name') === 'admin');
        $this->assertTrue($this->hconfig->get('admin.role') === '超级管理员');

    }

    public function testAddnewFile()
    {
        $this->hconfig->addFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->addFile($this->getConfigFilepath('role.php'),'role');
        $this->hconfig->load();

        $config = $this->hconfig->toArray();
        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($config['role']['name'] === '角色');
        $this->assertTrue($config['role']['sale']['name'] === '销售');
    }
}
