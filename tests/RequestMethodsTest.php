<?php
/**
 * php-guard/curl <https://github.com/php-guard/curl>
 * Copyright (C) ${YEAR} by Alexandre Le Borgne <alexandre.leborgne.83@gmail.com>.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PhpGuard\Curl\Tests;

use PhpGuard\Curl\Collection\Headers;
use PhpGuard\Curl\Curl;
use PhpGuard\Curl\CurlError;
use PHPUnit\Framework\TestCase;

class RequestMethodsTest extends TestCase
{
    private $curl;

    // https://docs.postman-echo.com/
    public function __construct()
    {
        parent::__construct();
        $this->curl = new Curl();
        $this->curl->getCurlRequestFactory()
            ->setSslVerifyPeer(false);
        $this->curl->getRequestModifierPipeline()
            //->pipe(new ProxyRequestModifier('X'))
        ;
    }

    public function testGet()
    {
        try {
            $data = ['foo1' => 'bar1', 'foo2' => 'bar2'];
            $response = $this->curl->get('https://postman-echo.com/get', $data);

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
            $response = $this->curl->post('https://postman-echo.com/post', $data);

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
            $response = $this->curl->post('https://postman-echo.com/post', $data);

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();
            $this->assertArrayHasKey('form', $response);
            $this->assertEquals($data, $response['form']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testPostJson()
    {
        try {
            $data = ['foo1' => 'bar1', 'foo2' => ['foo22' => 'bar2']];
            $response = $this->curl->post('https://postman-echo.com/post', $data, null, [
                Headers::CONTENT_TYPE => Headers::CONTENT_TYPE_FORM_JSON
            ]);
            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();
            $this->assertArrayHasKey('json', $response);
            $this->assertEquals($data, $response['json']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testPut()
    {
        try {
            $data = 'This is expected to be sent back as part of response body.';
            $response = $this->curl->put('https://postman-echo.com/put', $data);

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();

            $this->assertArrayHasKey('data', $response);
            $this->assertEquals($data, $response['data']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testPatch()
    {
        try {
            $data = 'This is expected to be sent back as part of response body.';
            $response = $this->curl->patch('https://postman-echo.com/patch', $data);

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();
            $this->assertArrayHasKey('data', $response);
            $this->assertEquals($data, $response['data']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testDelete()
    {
        try {
            $data = 'This is expected to be sent back as part of response body.';
            $response = $this->curl->delete('https://postman-echo.com/delete', $data);

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();
            $this->assertArrayHasKey('data', $response);
            $this->assertEquals($data, $response['data']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }
}
