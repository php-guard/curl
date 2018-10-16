<?php
/**
 * php-guard/curl <https://github.com/php-guard/curl>
 * Copyright (C) 2018 by Alexandre Le Borgne <alexandre.leborgne.83@gmail.com>.
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

use PhpGuard\Curl\CurlRequest;

class ProxyRequestModifier implements RequestModifierInterface
{
    /**
     * @var string
     */
    private $proxy;
    /**
     * @var array
     */
    private $proxyIgnoredHosts;
    /**
     * @var int
     */
    private $port;

    public function __construct(string $proxy, ?int $port = null, array $proxyIgnoredHosts = [])
    {
        $this->proxy = $proxy;
        $this->proxyIgnoredHosts = $proxyIgnoredHosts;
        $this->port = $port;
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * @param CurlRequest $request
     *
     * @return CurlRequest
     */
    public function modify(CurlRequest $request): CurlRequest
    {
        if (!in_array(parse_url($request->getUrl(), PHP_URL_HOST), $this->proxyIgnoredHosts)) {
            $request->getCurlOptions()[CURLOPT_HTTPPROXYTUNNEL] = true;
            $request->getCurlOptions()[CURLOPT_PROXY] = $this->proxy;
            if ($this->port) {
                $request->getCurlOptions()[CURLOPT_PROXYPORT] = $this->port;
            }
        }

        return $request;
    }
}
