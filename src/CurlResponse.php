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

namespace PhpGuard\Curl;

use PhpGuard\Curl\Collection\Headers;

class CurlResponse
{
    const JSON_PATTERN = '/^(?:application|text)\/(?:[a-z]+(?:[\.-][0-9a-z]+){0,}[\+\.]|x-)?json(?:-[a-z]+)?/i';
    const XML_PATTERN = '~^(?:text/|application/(?:atom\+|rss\+)?)xml~i';

    /**
     * @var string
     */
    private $rawResponse;

    /**
     * @var Headers
     */
    private $headers;

    /**
     * @var int
     */
    private $statusCode;
    /**
     * @var array
     */
    private $info;

    /**
     * CurlResponse constructor.
     *
     * @param int    $statusCode
     * @param string $rawResponse
     * @param array  $headers
     * @param array  $info
     */
    public function __construct(int $statusCode, string $rawResponse, array $headers, array $info)
    {
        $this->statusCode = $statusCode;
        $this->rawResponse = $rawResponse;
        $this->headers = new Headers($headers);
        $this->info = $info;
    }

    /**
     * @return int
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function isError(): bool
    {
        return $this->statusCode >= 300;
    }

    /**
     * @return Headers
     */
    public function headers(): Headers
    {
        return $this->headers;
    }

    public function raw()
    {
        return $this->rawResponse;
    }

    public function json()
    {
        if (!preg_match(self::JSON_PATTERN, $this->headers['Content-Type'])) {
            return false;
        }

        return json_decode($this->rawResponse, true);
    }
}
