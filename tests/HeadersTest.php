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

use PhpGuard\Curl\Curl;
use PhpGuard\Curl\CurlError;
use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{
    private $curl;

    // https://docs.postman-echo.com/
    public function __construct()
    {
        parent::__construct();
        $this->curl = new Curl();
        $this->curl->getCurlRequestFactory()->setSslVerifyPeer(false);
    }

    public function testRequest()
    {
        try {
            $data = 'Lorem ipsum dolor sit amet';
            $response = $this->curl->get('https://postman-echo.com/headers', null, [
                'my-sample-header' => $data,
            ])->execute();

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();

            $this->assertArrayHasKey('headers', $response);
            $this->assertArrayHasKey('my-sample-header', $response['headers']);
            $this->assertEquals($data, $response['headers']['my-sample-header']);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testResponse()
    {
        try {
            $data = [
                'Content-Type' => 'text/html',
                'test' => 'response_headers',
            ];
            $response = $this->curl->get('https://postman-echo.com/response-headers', $data)->execute();

            $this->assertEquals(200, $response->statusCode());
            $this->assertFalse($response->json());
            $this->assertEquals($data, json_decode($response->raw(), true));
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testBasicAuth()
    {
        try {
            $response = $this->curl->get('https://postman-echo.com/basic-auth', null, [
                'Authorization' => 'Basic cG9zdG1hbjpwYXNzd29yZA==',
            ])->execute();

            $this->assertEquals(200, $response->statusCode());
            $this->assertEquals(['authenticated' => true], $response->json());
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }
}
