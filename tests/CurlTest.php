<?php
/**
 * Created by PhpStorm.
 * User: GCC-MED
 * Date: 04/10/2018
 * Time: 13:07
 */

namespace PhpGuard\Curl\Tests;


use PhpGuard\Curl\Curl;
use PhpGuard\Curl\CurlError;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    private $curl;

    // https://docs.postman-echo.com/
    public function __construct()
    {
        parent::__construct();
        $this->curl = new Curl();
        $this->curl->setSslVerifyPeer(false);
    }

    public function testGet()
    {
        try {
            $data = ['foo1' => 'bar1', 'foo2' => 'bar2'];
            $response = $this->curl->get('https://postman-echo.com/get', $data)
                ->execute()->json();

            $this->assertArrayHasKey('args', $response);
            $this->assertSame($data, $response['args']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }
}