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

class FileRequestModifier implements RequestModifierInterface
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
        $data = $request->getData();
        $headers = $request->getHeaders();

        if (is_array($data)) {
            if (!preg_match(Headers::CONTENT_TYPE_PATTERN_JSON, $headers[Headers::CONTENT_TYPE])) {
                $hasFile = false;
                foreach ($data as $key => $value) {
                    if (is_string($value) && 0 === strpos($value, '@') && is_file(substr($value, 1))) {
                        $hasFile = true;
                        if (class_exists('CURLFile')) {
                            $data[$key] = new \CURLFile(substr($value, 1));
                        }
                    } elseif ($value instanceof \CURLFile) {
                        $hasFile = true;
                    }
                }

                if ($hasFile) {
                    $headers[Headers::CONTENT_TYPE] = Headers::CONTENT_TYPE_MULTIPART_FORM_DATA;
                } else {
                    $headers[Headers::CONTENT_TYPE] = Headers::CONTENT_TYPE_FORM_URL_ENCODED;

                    $data = http_build_query($data, '', '&');
                }

                $request->setData($data);
            }
        }

        return $request;
    }
}
