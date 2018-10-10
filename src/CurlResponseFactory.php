<?php
/**
 * php-guard/curl <https://github.com/php-guard/curl>
 * Copyright (C) 2018 by Alexandre Le Borgne <alexandre.leborgne.83@gmail.com>
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


class CurlResponseFactory
{
    public function create($result, array $info)
    {
        $statusCode = $info['http_code'];
        $headerSize = $info['header_size'];
        $rawHeaders = substr($result, 0, $headerSize);
        $raw = substr($result, $headerSize);

        $headers = array_reduce(explode("\n", $rawHeaders), function ($headers, $header) {
            $parts = explode(':', $header);
            if (count($parts) == 2) {
                $headers[trim($parts[0])] = trim($parts[1]);
            }
            return $headers;
        }, []);

        return new CurlResponse($statusCode, $raw, $headers, $info);
    }
}