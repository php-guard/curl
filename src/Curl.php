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
            ->pipe(new PlainTextRequestModifier());
    }

    /**
     * @param CurlRequest[] $requests
     * @param array         $options  Définit les options pour le gestionnaire multiple cURL
     *
     * @return CurlResponse[]
     *
     * @throws CurlError
     */
    public function multi(array $requests, array $options = [])
    {
        $mh = curl_multi_init();

        foreach ($options as $key => $value) {
            curl_multi_setopt($mh, $key, $value);
        }

        $chs = [];
        foreach ($requests as $request) {
            $ch = $this->prepare($request);
            $chs[] = $ch;
            curl_multi_add_handle($mh, $ch);
        }

        $active = null;

        do {
            $code = curl_multi_exec($mh, $active);
            curl_multi_select($mh);
        } while ($active > 0);

        $responses = [];

        try {
            if ($code > CURLM_OK) {
                throw new CurlError(curl_multi_strerror($code), $code);
            }

            foreach ($chs as $ch) {
                $output = curl_multi_getcontent($ch);

                if (false === $output) {
                    $message = curl_error($ch);
                    $code = curl_errno($ch);

                    throw new CurlError($message, $code);
                }

                $responses[] = $this->curlResponseFactory->create($output, curl_getinfo($ch));
            }
        } catch (CurlError $curlError) {
            throw $curlError;
        } finally {
            foreach ($chs as $ch) {
                curl_multi_remove_handle($mh, $ch);
            }
            curl_multi_close($mh);
        }

        return $responses;
    }

    /**
     * @param CurlRequest $request
     *
     * @return resource
     */
    protected function prepare(CurlRequest $request)
    {
        $request = $this->requestModifierPipeline->process($request);

        // create curl resource
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $request->getUrl());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getData());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders()->toHttp());

        foreach ($request->getCurlOptions()->all() as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        return $ch;
    }

    /**
     * @param string            $url
     * @param null|array|string $query
     * @param array             $headers
     *
     * @return CurlRequest
     */
    public function get(string $url, $query = null, array $headers = [])
    {
        return $this->curlRequestFactory->create('GET', $url, null, $query, $headers);
    }

    public function post(string $url, $data = null, $query = null, array $headers = [])
    {
        return $this->curlRequestFactory->create('POST', $url, $data, $query, $headers);
    }

    public function put(string $url, $data = null, $query = null, array $headers = [])
    {
        return $this->curlRequestFactory->create('PUT', $url, $data, $query, $headers);
    }

    public function patch(string $url, $data = null, $query = null, array $headers = [])
    {
        return $this->curlRequestFactory->create('PATCH', $url, $data, $query, $headers);
    }

    public function delete(string $url, $data = null, $query = null, array $headers = [])
    {
        return $this->curlRequestFactory->create('DELETE', $url, $data, $query, $headers);
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
        $ch = $this->prepare($request);

        // $output contains the output string
        $output = curl_exec($ch);

        if (false === $output) {
            $message = curl_error($ch);
            $code = curl_errno($ch);

            // close curl resource to free up system resources
            curl_close($ch);
            throw new CurlError($message, $code);
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        return $this->curlResponseFactory->create($output, $info);
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
