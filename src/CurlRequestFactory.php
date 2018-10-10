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


class CurlRequestFactory
{
    const DEFAULT_CURL_OPTIONS = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    ];

    protected $defaultCurlOptions;
    protected $defaultHeaders;
    /**
     * @var null|string
     */
    private $host;
    /**
     * @var Curl
     */
    private $curl;

    public function __construct(Curl $curl, ?string $host = null)
    {
        if ($host) {
            $host = rtrim($host, '/');
        }

        $this->defaultHeaders = new Headers();
        $this->defaultCurlOptions = new CurlOptions(self::DEFAULT_CURL_OPTIONS);
        $this->host = $host;
        $this->curl = $curl;
    }

    public function create(string $method, string $url, $data = null, $query = null, array $headers = []) {
        if ($this->host && is_null(parse_url($url, PHP_URL_HOST))) {
            $url = $this->host . $url;
        }

        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        $headers = $this->defaultHeaders->replace($headers)->all();
        return new CurlRequest($this->curl, $url, $method, $data, $headers, $this->defaultCurlOptions->all());
    }

    /**
     * If set to false, ignore error "SSL certificate problem: unable to get local issuer certificate"
     * Default to true
     * @param bool $value
     * @return CurlRequestFactory
     */
    public function setSslVerifyPeer(bool $value): self
    {
        $this->defaultCurlOptions[CURLOPT_SSL_VERIFYPEER] = $value;

        return $this;
    }

    public function getDefaultCurlOptions(): CurlOptions
    {
        return $this->defaultCurlOptions;
    }

    /**
     * @return Headers
     */
    public function getDefaultHeaders(): Headers
    {
        return $this->defaultHeaders;
    }

    /**
     * @return null|string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param null|string $host
     * @return CurlRequestFactory
     */
    public function setHost(?string $host): self
    {
        if($host) {
            $host = rtrim($host, '/');
        }

        $this->host = $host;

        return $this;
    }
}