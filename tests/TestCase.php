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
    protected function setUp()
    {
        $this->hconfig = new Configure();
        $this->hconfig->setFiles();

        $this->config = parse_ini_file(dirname(__DIR__) . '/test.ini');
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown()
    {

    }

    // 整个测试类之前
    public static function setUpBeforeClass()
    {

    }

    // 整个测试类之前
    public static function tearDownAfterClass()
    {

    }


}
