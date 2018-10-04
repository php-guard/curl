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
            $response = $this->curl->get('https://postman-echo.com/get', $data)->execute();

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();
            $this->assertArrayHasKey('args', $response);
            $this->assertEquals($data, $response['args']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testPostRawText()
    {
        try {
            $data = 'This is expected to be sent back as part of response body.';
            $response = $this->curl->post('https://postman-echo.com/post', $data)->execute();

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();
            $this->assertArrayHasKey('data', $response);
            $this->assertEquals($data, $response['data']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testPostFormData()
    {
        try {
            $data = ['foo1' => 'bar1', 'foo2' => 'bar2'];
            $response = $this->curl->post('https://postman-echo.com/post', $data)->execute();

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();
            $this->assertArrayHasKey('form', $response);
            $this->assertEquals($data, $response['form']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }
}