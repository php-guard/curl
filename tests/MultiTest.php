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

class MultiTest extends TestCase
{
    private $curl;

    public function __construct()
    {
        parent::__construct();
        $this->curl = new Curl();
        $this->curl->getCurlRequestFactory()->setSslVerifyPeer(false);
    }

    public function testMulti()
    {
        $array = ['foo1' => 'bar1', 'foo2' => 'bar2'];
        $string = 'This is expected to be sent back as part of response body.';

        try {
            $responses = $this->curl->multi([
                $this->curl->get('https://postman-echo.com/get', $array),
                $this->curl->post('https://postman-echo.com/post', $array),
                $this->curl->post('https://postman-echo.com/post', $string),
            ]);

            foreach ($responses as $response) {
                $this->assertFalse($response->isError(), $response->statusCode() . ' ' . $response->raw());
            }
        } catch (CurlError $e) {
            $this->fail('(' . $e->getCode() . ') ' . $e->getMessage());
        }
    }
}
