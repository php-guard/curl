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

namespace PhpGuard\Curl;


class CurlRequest
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var Headers
     */
    protected $headers;

    /**
     * CurlRequest constructor.
     * @param Curl $curl
     * @param string $url
     * @param string $method
     * @param array|null $data
     * @param array|Headers $headers
     */
    public function __construct(Curl $curl, string $url, string $method = 'GET', $data = null, $headers = [])
    {
        $this->curl = $curl;
        $this->url = $url;
        $this->method = $method;
        $this->data = $data;
        $this->headers = $headers instanceof Headers ? $headers : new Headers($headers);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return Headers
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    public function setHeaderContentType(string $contentType) {
        $this->headers['Content-Type'] = $contentType;
    }

    /**
     * @param bool $throwExceptionOnHttpError
     * @return CurlResponse
     * @throws CurlError
     */
    public function execute(bool $throwExceptionOnHttpError = false): CurlResponse
    {
        $response = $this->curl->execute($this);

        if ($throwExceptionOnHttpError && $response->isError()) {
            throw new CurlError($response->raw(), $response->statusCode());
        }

        return $response;
    }
}