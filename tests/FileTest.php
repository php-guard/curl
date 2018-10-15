<?php
/**
 * php-guard/curl <https://github.com/php-guard/curl>
 * Copyright (C) ${YEAR} by Alexandre Le Borgne <alexandre.leborgne.83@gmail.com>
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

class FileTest extends TestCase
{
    private $curl;

    public function __construct()
    {
        parent::__construct();
        $this->curl = new Curl();
        $this->curl->getCurlRequestFactory()->setSslVerifyPeer(false);
    }

    public function testPost()
    {
        try {
            $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'LICENSE';
            $content = base64_encode(file_get_contents($file));
            $name = basename($file);

            $response = $this->curl->post('https://postman-echo.com/post', [
                'file' => '@'.$file,
                'name' => $name,
            ])->execute();

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();

            $this->assertArrayHasKey('name', $response['form']);
            $this->assertEquals($name, $response['form']['name']);

            $this->assertArrayHasKey($name, $response['files']);
            $data = explode(',', $response['files'][$name])[1] ?? null;
            $this->assertEquals($content, $data);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testPostCurl()
    {
        try {
            $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'LICENSE';
            $content = base64_encode(file_get_contents($file));
            $name = basename($file);
            $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);

            $data = new \CURLFile($file, $mimeType, $name);

            $response = $this->curl->post('https://postman-echo.com/post', [
                'file' => $data,
                'name' => $name,
            ])->execute();

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();

            $this->assertArrayHasKey('name', $response['form']);
            $this->assertEquals($name, $response['form']['name']);

            $this->assertArrayHasKey($name, $response['files']);
            $data = explode(',', $response['files'][$name])[1] ?? null;
            $this->assertEquals($content, $data);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testPut()
    {
        try {
            $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'LICENSE';
            $content = base64_encode(file_get_contents($file));
            $name = basename($file);

            $response = $this->curl->post('https://postman-echo.com/post', [
                'file' => '@'.$file,
                'name' => $name,
            ])->execute();

            $this->assertEquals(200, $response->statusCode());
            $response = $response->json();

            $this->assertArrayHasKey('name', $response['form']);
            $this->assertEquals($name, $response['form']['name']);

            $this->assertArrayHasKey($name, $response['files']);
            $data = explode(',', $response['files'][$name])[1] ?? null;
            $this->assertEquals($content, $data);
        } catch (CurlError $e) {
            $this->fail($e->getMessage());
        }
    }
}
