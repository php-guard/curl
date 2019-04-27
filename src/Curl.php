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

use PhpGuard\Curl\RequestModifier\DataRequestModifier;
use PhpGuard\Curl\RequestModifier\FileRequestModifier;
use PhpGuard\Curl\RequestModifier\PlainTextRequestModifier;

class Curl
{
    private $requestModifierPipeline;
    private $curlResponseFactory;
    private $curlRequestFactory;

    public function __construct(?string $host = null)
    {
        $this->requestModifierPipeline = new RequestModifierPipeline();
        $this->curlResponseFactory = new CurlResponseFactory();
        $this->curlRequestFactory = new CurlRequestFactory($this, $host);

        $this->requestModifierPipeline
            ->pipe(new FileRequestModifier())
            ->pipe(new DataRequestModifier())
            ->pipe(new PlainTextRequestModifier());
    }

    /**
     * @param string $url
     * @param mixed  $query
     * @param array  $headers
     * @param bool   $throwExceptionOnHttpError
     *
     * @return CurlResponse
     *
     * @throws CurlError
     */
    public function get(string $url, $query = null, array $headers = [], bool $throwExceptionOnHttpError = false): CurlResponse
    {
        return $this->curlRequestFactory->create('GET', $url, null, $query, $headers)->execute($throwExceptionOnHttpError);
    }

    /**
     * @param string $url
     * @param mixed  $data
     * @param mixed  $query
     * @param array  $headers
     * @param bool   $throwExceptionOnHttpError
     *
     * @return CurlResponse
     *
     * @throws CurlError
     */
    public function post(string $url, $data = null, $query = null, array $headers = [], bool $throwExceptionOnHttpError = false): CurlResponse
    {
        return $this->curlRequestFactory->create('POST', $url, $data, $query, $headers)->execute($throwExceptionOnHttpError);
    }

    /**
     * @param string $url
     * @param mixed  $data
     * @param mixed  $query
     * @param array  $headers
     * @param bool   $throwExceptionOnHttpError
     *
     * @return CurlResponse
     *
     * @throws CurlError
     */
    public function put(string $url, $data = null, $query = null, array $headers = [], bool $throwExceptionOnHttpError = false): CurlResponse
    {
        return $this->curlRequestFactory->create('PUT', $url, $data, $query, $headers)->execute($throwExceptionOnHttpError);
    }

    /**
     * @param string $url
     * @param mixed  $data
     * @param mixed  $query
     * @param array  $headers
     * @param bool   $throwExceptionOnHttpError
     *
     * @return CurlResponse
     *
     * @throws CurlError
     */
    public function patch(string $url, $data = null, $query = null, array $headers = [], bool $throwExceptionOnHttpError = false): CurlResponse
    {
        return $this->curlRequestFactory->create('PATCH', $url, $data, $query, $headers)->execute($throwExceptionOnHttpError);
    }

    /**
     * @param string $url
     * @param mixed  $data
     * @param mixed  $query
     * @param array  $headers
     * @param bool   $throwExceptionOnHttpError
     *
     * @return CurlResponse
     *
     * @throws CurlError
     */
    public function delete(string $url, $data = null, $query = null, array $headers = [], bool $throwExceptionOnHttpError = false): CurlResponse
    {
        return $this->curlRequestFactory->create('DELETE', $url, $data, $query, $headers)->execute($throwExceptionOnHttpError);
    }

    /**
     * @param array $options Définit les options pour le gestionnaire multiple cURL
     *
     * @return MultiCurl
     */
    public function multi(array $options = [])
    {
        return new MultiCurl($this, $options);
    }

    /**
     * @param CurlRequest $request
     *
     * @return CurlResponse
     *
     * @throws CurlError
     */
    public function execute(CurlRequest $request): CurlResponse
    {
        $ch = $this->requestModifierPipeline->process($request)->resource();

        try {
            return $this->curlResponseFactory->create($ch, curl_exec($ch));
        } finally {
            curl_close($ch);
        }
    }

    /**
     * @return RequestModifierPipeline
     */
    public function getRequestModifierPipeline(): RequestModifierPipeline
    {
        return $this->requestModifierPipeline;
    }

    /**
     * @return CurlRequestFactory
     */
    public function getCurlRequestFactory(): CurlRequestFactory
    {
        return $this->curlRequestFactory;
    }

    /**
     * @return CurlResponseFactory
     */
    public function getCurlResponseFactory(): CurlResponseFactory
    {
        return $this->curlResponseFactory;
    }
}
