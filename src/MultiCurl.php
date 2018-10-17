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

class MultiCurl
{
    protected $requests;
    /**
     * @var CurlRequestFactory
     */
    private $curlRequestFactory;
    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var array
     */
    private $options;

    public function __construct(Curl $curl, array $options = [])
    {
        $this->requests = [];
        $this->curl = $curl;
        $this->options = $options;
        $this->curlRequestFactory = $curl->getCurlRequestFactory();
    }

    /**
     * @param string $url
     * @param mixed  $query
     * @param array  $headers
     *
     * @return MultiCurl
     */
    public function get(string $url, $query = null, array $headers = []): self
    {
        $this->requests[] = $this->curlRequestFactory->create('GET', $url, null, $query, $headers);

        return $this;
    }

    /**
     * @param string $url
     * @param mixed  $data
     * @param mixed  $query
     * @param array  $headers
     *
     * @return MultiCurl
     */
    public function post(string $url, $data = null, $query = null, array $headers = []): self
    {
        $this->requests[] = $this->curlRequestFactory->create('POST', $url, $data, $query, $headers);

        return $this;
    }

    /**
     * @param string $url
     * @param mixed  $data
     * @param mixed  $query
     * @param array  $headers
     *
     * @return MultiCurl
     */
    public function put(string $url, $data = null, $query = null, array $headers = []): self
    {
        $this->requests[] = $this->curlRequestFactory->create('PUT', $url, $data, $query, $headers);

        return $this;
    }

    /**
     * @param string $url
     * @param mixed  $data
     * @param mixed  $query
     * @param array  $headers
     *
     * @return MultiCurl
     */
    public function patch(string $url, $data = null, $query = null, array $headers = []): self
    {
        $this->requests[] = $this->curlRequestFactory->create('PATCH', $url, $data, $query, $headers);

        return $this;
    }

    /**
     * @param string $url
     * @param mixed  $data
     * @param mixed  $query
     * @param array  $headers
     *
     * @return MultiCurl
     */
    public function delete(string $url, $data = null, $query = null, array $headers = []): self
    {
        $this->requests[] = $this->curlRequestFactory->create('DELETE', $url, $data, $query, $headers);

        return $this;
    }

    /**
     * @return CurlResponse[]
     *
     * @throws CurlError
     */
    public function execute()
    {
        $mh = curl_multi_init();

        foreach ($this->options as $key => $value) {
            curl_multi_setopt($mh, $key, $value);
        }

        $chs = [];
        foreach ($this->requests as $request) {
            $ch = $this->curl->getRequestModifierPipeline()->process($request)->resource();
            $chs[] = $ch;
            curl_multi_add_handle($mh, $ch);
        }

        $active = null;

        do {
            $code = curl_multi_exec($mh, $active);
            curl_multi_select($mh);
        } while ($active > 0);

        try {
            if ($code > CURLM_OK) {
                throw new CurlError(curl_multi_strerror($code), $code);
            }

            $responses = [];
            foreach ($chs as $ch) {
                $responses[] = $this->curl->getCurlResponseFactory()->create($ch, curl_multi_getcontent($ch));
            }

            return $responses;
        } finally {
            foreach ($chs as $ch) {
                curl_multi_remove_handle($mh, $ch);
            }
            curl_multi_close($mh);
        }
    }
}
