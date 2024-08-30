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
    }

    protected function getConfigFilepath(string $filename)
    {
        return __DIR__ . '/../common/' . $filename;
    }

    public function testPhp()
    {
        $this->hconfig->setFiles($this->getConfigFilepath('user.php'));

        $config = $this->hconfig->getConfig();
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
        $this->hconfig->setFiles($this->getConfigFilepath('admin.ini'));
        $fileConfig = $this->hconfig->getRawConfigFromFile();

        $this->assertTrue($fileConfig['username'] === 'admin');
        $this->assertTrue($fileConfig['email'] === 'admin@admin.com');

        $config = $this->hconfig->getConfig();
        $this->assertTrue($config['username'] === 'admin');
        $this->assertTrue($config['email'] === 'admin@admin.com');

        $this->assertTrue($this->hconfig->has('username'));
        $this->assertTrue(!$this->hconfig->has('usernamex'));
    }

    public function testJson()
    {
        $this->hconfig->setFiles($this->getConfigFilepath('config.json'));
        $fileConfig = $this->hconfig->getRawConfigFromFile();

        $this->assertTrue($fileConfig['name'] === 'hehe');
        $this->assertTrue($fileConfig['age'] === 18);

        $config = $this->hconfig->getConfig();
        $this->assertTrue($config['name'] === 'hehe');
        $this->assertTrue($config['age'] === 18);

        $this->assertTrue($this->hconfig->has('name'));
        $this->assertTrue(!$this->hconfig->has('namex'));
    }

    public function testYaml()
    {
        $this->hconfig->setFiles($this->getConfigFilepath('app.yaml'));
        $fileConfig = $this->hconfig->getRawConfigFromFile();

        $this->assertTrue($fileConfig['runtime'] === 'go');
        $this->assertTrue($fileConfig['api_version'] === 'go1');

        $config = $this->hconfig->getConfig();
        $this->assertTrue($config['runtime'] === 'go');
        $this->assertTrue($config['api_version'] === 'go1');

        $this->assertTrue($config['handlers']['login'] === 'required');

        $this->assertTrue($this->hconfig->has('runtime'));
        $this->assertTrue(!$this->hconfig->has('runtimex'));
    }

    public function testXml()
    {
        $this->hconfig->setFiles($this->getConfigFilepath('wechat.xml'));
        $fileConfig = $this->hconfig->getRawConfigFromFile();

        $this->assertTrue($fileConfig['username'] === 'hehex');
        $this->assertTrue($fileConfig['password'] === '123456');

        $config = $this->hconfig->getConfig();
        $this->assertTrue($config['username'] === 'hehex');
        $this->assertTrue($config['password'] === '123456');

        $this->assertTrue($this->hconfig->get('admin.name') === 'admin');
        $this->assertTrue($this->hconfig->get('admin.role') === '超级管理员');

        $this->assertTrue($this->hconfig->has('username'));
        $this->assertTrue(!$this->hconfig->has('usernamex'));
    }

    public function testMoreFile()
    {
        $this->hconfig->setFiles($this->getConfigFilepath('wechat.xml'));
        $this->hconfig->setFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->setFiles($this->getConfigFilepath('admin.ini'));

        $config = $this->hconfig->getConfig();
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
        $this->hconfig->setFiles($this->getConfigFilepath('wechat.xml'));
        $this->hconfig->setFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->setFiles($this->getConfigFilepath('admin.ini'));
        $this->hconfig->setCacheFile($this->config['cachefile']);

        $config = $this->hconfig->getConfig();
        // php
        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($config['username'] === 'hehex');

        // ini
        $this->assertTrue($config['enabled'] === '1');

        // xml
        $this->assertTrue($this->hconfig->get('admin.name') === 'admin');
        $this->assertTrue($this->hconfig->get('admin.role') === '超级管理员');


        // 验证缓存文件与配置文件是否一致
        $config = $this->hconfig->getRawConfigFromFile();

        $this->assertTrue(json_encode($config) === json_encode(require($this->config['cachefile'])));
    }

    public function testCacheFile1()
    {
        $this->hconfig->setFiles($this->getConfigFilepath('wechat.xml'));
        $this->hconfig->setFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->setFiles($this->getConfigFilepath('admin.ini'));
        $this->hconfig->setCacheFile($this->config['cachefile']);

        $config = $this->hconfig->getConfig();
        // php
        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($config['username'] === 'hehex');

        // ini
        $this->assertTrue($config['enabled'] === '1');

        // xml
        $this->assertTrue($this->hconfig->get('admin.name') === 'admin');
        $this->assertTrue($this->hconfig->get('admin.role') === '超级管理员');


        // 验证缓存文件与配置文件是否一致
        $cacheConfig = $this->hconfig->getCacheConfigFromFile();

        // php
        $this->assertTrue($cacheConfig['app_name'] === 'hehe');
        $this->assertTrue($cacheConfig['username'] === 'hehex');

        // ini
        $this->assertTrue($cacheConfig['enabled'] === '1');

    }

    public function testParser()
    {
        $this->hconfig->setFiles($this->getConfigFilepath('admin.ini'));
        $this->hconfig->setParser('ini',[IniParser::class,'parse']);

        $config = $this->hconfig->getConfig();
        $this->assertTrue($config['username'] === 'admin');
        $this->assertTrue($config['email'] === 'admin@admin.com');
        $this->assertTrue($this->hconfig->has('username'));
        $this->assertTrue(!$this->hconfig->has('usernamex'));

    }

    public function testParser1()
    {
        $this->hconfig->setFiles($this->getConfigFilepath('admin.ini'));
        $this->hconfig->setParser('ini',function($file){
            return parse_ini_string(file_get_contents($file), true);
        });

        $config = $this->hconfig->getConfig();
        $this->assertTrue($config['username'] === 'admin');
        $this->assertTrue($config['email'] === 'admin@admin.com');
        $this->assertTrue($this->hconfig->has('username'));
        $this->assertTrue(!$this->hconfig->has('usernamex'));

    }

    public function testConfigItem()
    {
        $this->hconfig->setFiles($this->getConfigFilepath('user.php'));
        $this->hconfig->setFiles([$this->getConfigFilepath('role.php'),'role']);

        $config = $this->hconfig->getConfig();
        $this->assertTrue($config['app_name'] === 'hehe');
        $this->assertTrue($config['role']['name'] === '角色');
        $this->assertTrue($config['role']['sale']['name'] === '销售');

    }
}
