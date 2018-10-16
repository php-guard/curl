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

class CurlResponseFactory
{
    /**
     * @param $ch
     * @param $content
     *
     * @return CurlResponse
     *
     * @throws CurlError
     */
    public function create($ch, $content)
    {
        if (false === $content) {
            $message = curl_error($ch);
            $code = curl_errno($ch);

            throw new CurlError($message, $code);
        }

        $info = curl_getinfo($ch);

        $statusCode = $info['http_code'];
        $headerSize = $info['header_size'];
        $rawHeaders = substr($content, 0, $headerSize);
        $raw = substr($content, $headerSize);

        $headers = array_reduce(explode("\n", $rawHeaders), function ($headers, $header) {
            $parts = explode(':', $header);
            if (2 == count($parts)) {
                $headers[trim($parts[0])] = trim($parts[1]);
            }

            return $headers;
        }, []);

        return new CurlResponse($statusCode, $raw, $headers, $info);
    }
}
