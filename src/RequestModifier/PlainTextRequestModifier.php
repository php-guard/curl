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

namespace PhpGuard\Curl\RequestModifier;

use PhpGuard\Curl\Collection\Headers;
use PhpGuard\Curl\CurlRequest;

class PlainTextRequestModifier implements RequestModifierInterface
{
    /**
     * Modify a request.
     *
     * @param CurlRequest $request
     *
     * @return CurlRequest
     */
    public function modify(CurlRequest $request): CurlRequest
    {
        $headers = $request->getHeaders();
        if (is_string($request->getData()) && !isset($headers[Headers::CONTENT_TYPE])) {
            $headers[Headers::CONTENT_TYPE] = Headers::CONTENT_TYPE_TEXT_PLAIN;
        }

        return $request;
    }
}
