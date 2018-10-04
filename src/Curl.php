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


class Curl
{
    const DEFAULT_CURL_OPTIONS = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_ENCODING => "",
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    ];

    protected $curlOptions = [];
    protected $defaultHeaders;

    public function __construct()
    {
        $this->defaultHeaders = new Headers();
    }

    /**
     * If set to false, ignore error "SSL certificate problem: unable to get local issuer certificate"
     * Default to true
     * @param bool $value
     */
    public function setSslVerifyPeer(bool $value)
    {
        $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $value);
    }

    public function setCurlOption(int $key, $value)
    {
        $this->curlOptions[$key] = $value;
    }

    /**
     * @param string $url
     * @param null|array|string $query
     * @param array $headers
     * @return CurlRequest
     */
    public function get(string $url, $query = null, array $headers = [])
    {
        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        return new CurlRequest($this, $url, 'GET', null, $this->defaultHeaders->replace($headers));
    }

    public function post(string $url, $data = null, $query = null, array $headers = [])
    {
        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        return new CurlRequest($this, $url, 'POST', $data, $this->defaultHeaders->replace($headers));
    }

    /**
     * @param CurlRequest $request
     * @return CurlResponse
     * @throws CurlError
     */
    public function execute(CurlRequest $request): CurlResponse
    {
        // create curl resource
        $ch = curl_init();

        // set url
        $url = $request->getUrl();
        $data = $request->getData();

        if ($request->getMethod() == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        if (!empty($request->getData())) {
            if (!isset($request->getHeaders()[Headers::CONTENT_TYPE])) {
                if (is_string($request->getData())) {
                    $request->getHeaders()[Headers::CONTENT_TYPE] = Headers::CONTENT_TYPE_TEXT_PLAIN;
                } elseif (is_array($request->getData())) {
                    $request->getHeaders()[Headers::CONTENT_TYPE] = Headers::CONTENT_TYPE_FORM_URL_ENCODED;
                    $data = http_build_query($data, '', '&');
                }
            } else if (preg_match(Headers::CONTENT_TYPE_PATTERN_JSON, $request->getHeaders()[Headers::CONTENT_TYPE])) {
                $data = json_encode($data);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());

        $curlOptions = array_replace(self::DEFAULT_CURL_OPTIONS, $this->curlOptions);
        foreach ($curlOptions as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders()->toHttp());

        // $output contains the output string
        $output = curl_exec($ch);

        if ($output === false) {
            $message = curl_error($ch);
            $code = curl_errno($ch);

            // close curl resource to free up system resources
            curl_close($ch);
            throw new CurlError($message, $code);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rawHeaders = substr($output, 0, $headerSize);
        $raw = substr($output, $headerSize);

        $headers = array_reduce(explode("\n", $rawHeaders), function ($headers, $header) {
            $parts = explode(':', $header);
            if (count($parts) == 2) {
                $headers[trim($parts[0])] = trim($parts[1]);
            }
            return $headers;
        }, []);

        // close curl resource to free up system resources
        curl_close($ch);

        return new CurlResponse($statusCode, $raw, new Headers($headers));
    }

    /**
     * @param CurlRequest[] $requests
     * @return CurlResponse[]
     */
    public function executeMulti(array $requests): array
    {
        return [new CurlResponse()];
    }

    /**
     * @return Headers
     */
    public function getDefaultHeaders(): Headers
    {
        return $this->defaultHeaders;
    }
}