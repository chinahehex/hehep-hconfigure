<?php
namespace hconfigure\tests;

use hehe\core\hconfigure\Configure;


class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Configure
     */
    protected $hconfig;

    protected $config = [];

    // 单个测试之前(每个测试方法之前调用)
    protected function setUp():void
    {
        $this->config = parse_ini_file(dirname(__DIR__) . '/test.ini');
        $this->hconfig = new Configure([
            'configFiles'=>[
                __DIR__ . '/common/defaultconfig.php',
            ],
        ]);
        $this->hconfig->setCacheFile($this->config['cachefile']);


    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown():void
    {

    }

    // 整个测试类之前
    public static function setUpBeforeClass():void
    {

    }

    // 整个测试类之前
    public static function tearDownAfterClass():void
    {

    }


}
