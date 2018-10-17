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

class OptionsTest extends TestCase
{
    private $curl;

    public function __construct()
    {
        parent::__construct();
        $this->curl = new Curl();
        $this->curl->getCurlRequestFactory()->setSslVerifyPeer(false);
    }

    public function testBaseUrl()
    {
        try {
            $response = $this->curl->get('https://postman-echo.com/get');
            $this->assertEquals(200, $response->statusCode());
        } catch (CurlError $e) {
            $this->fail('('.$e->getCode().') '.$e->getMessage());
        }

        try {
            $this->curl->get('/get');
            $this->fail('Expect exception CurlError "(3) <url> malformed"');

            $this->expectException(CurlError::class);
            $this->expectExceptionCode(CURLE_URL_MALFORMAT);
        } catch (CurlError $e) {
            $this->assertEquals(3, $e->getCode());
        }

        try {
            $this->curl->getCurlRequestFactory()->setBaseUrl('https://postman-echo.com/');
            $response = $this->curl->get('get');
            $this->assertEquals(200, $response->statusCode());
        } catch (CurlError $e) {
            $this->fail('('.$e->getCode().') '.$e->getMessage());
        }
    }
}
