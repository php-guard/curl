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


class Curl
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

    protected $curlOptions = [];
    protected $defaultHeaders;
    /**
     * @var null|string
     */
    private $baseUrl;
    /**
     * @var null|string
     */
    private $proxy;
    /**
     * @var array
     */
    private $proxyIgnoredHosts;

    public function __construct(?string $baseUrl = null, ?string $proxy = null, array $proxyIgnoredHosts = [])
    {
        if($baseUrl) {
            $baseUrl = rtrim($baseUrl, '/');
        }

        $this->defaultHeaders = new Headers();
        $this->baseUrl = $baseUrl;
        $this->proxy = $proxy;
        $this->proxyIgnoredHosts = $proxyIgnoredHosts;
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

    public function getCurlOption(int $key) {
        return $this->curlOptions[$key] ?? null;
    }

    /**
     * @return Headers
     */
    public function getDefaultHeaders(): Headers
    {
        return $this->defaultHeaders;
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

        if($this->baseUrl && is_null(parse_url($url, PHP_URL_HOST))) {
            $url = $this->baseUrl . $url;
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

    public function put(string $url, $data = null, $query = null, array $headers = [])
    {
        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        return new CurlRequest($this, $url, 'PUT', $data, $this->defaultHeaders->replace($headers));
    }

    public function patch(string $url, $data = null, $query = null, array $headers = [])
    {
        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        return new CurlRequest($this, $url, 'PATCH', $data, $this->defaultHeaders->replace($headers));
    }

    public function delete(string $url, $data = null, $query = null, array $headers = [])
    {
        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        return new CurlRequest($this, $url, 'DELETE', $data, $this->defaultHeaders->replace($headers));
    }

    /**
     * @param CurlRequest $request
     * @return resource
     */
    public function prepare(CurlRequest $request)
    {
        // create curl resource
        $ch = curl_init();

        // set url
        $url = $request->getUrl();
        $data = $request->getData();

        if ($this->proxy && !in_array(parse_url($request->getUrl(), PHP_URL_HOST), $this->proxyIgnoredHosts)) {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }

        if ($request->getMethod() == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        if (!empty($data)) {
            if (is_string($data)) {
                if (!isset($request->getHeaders()[Headers::CONTENT_TYPE])) {
                    $request->getHeaders()[Headers::CONTENT_TYPE] = Headers::CONTENT_TYPE_TEXT_PLAIN;
                }
            }
            else if (is_array($data)) {
                if (preg_match(Headers::CONTENT_TYPE_PATTERN_JSON, $request->getHeaders()[Headers::CONTENT_TYPE])) {
                    $data = json_encode($data);
                }
                else {
                    $hasFile = false;
                    foreach ($data as $key => $value) {
                        if (is_string($value) && strpos($value, '@') === 0 && is_file(substr($value, 1))) {
                            $hasFile = true;
                            if (class_exists('CURLFile')) {
                                $data[$key] = new \CURLFile(substr($value, 1));
                            }
                        }
                        else if ($value instanceof \CURLFile) {
                            $hasFile = true;
                        }
                    }

                    if ($hasFile) {
                        $request->getHeaders()[Headers::CONTENT_TYPE] = Headers::CONTENT_TYPE_MULTIPART_FORM_DATA;
                    }
                    else {
                        $request->getHeaders()[Headers::CONTENT_TYPE] = Headers::CONTENT_TYPE_FORM_URL_ENCODED;
                        $data = http_build_query($data, '', '&');
                    }
                }
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

        return $ch;
    }

    /**
     * @param CurlRequest|resource $request
     * @return CurlResponse
     * @throws CurlError
     */
    public function execute($request): CurlResponse
    {
       $ch = $request instanceof CurlRequest ? $this->prepare($request) : $request;

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

}