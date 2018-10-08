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
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    ];

    protected $defaultCurlOptions;
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
    private $requestModifierPipeline;

    public function __construct(?string $baseUrl = null, ?string $proxy = null, array $proxyIgnoredHosts = [])
    {
        if($baseUrl) {
            $baseUrl = rtrim($baseUrl, '/');
        }

        $this->defaultHeaders = new Headers();
        $this->defaultCurlOptions = new CurlOptions(self::DEFAULT_CURL_OPTIONS);
        $this->baseUrl = $baseUrl;
        $this->proxy = $proxy;
        $this->proxyIgnoredHosts = $proxyIgnoredHosts;
        $this->requestModifierPipeline = new RequestModifierPipeline();

        $this->requestModifierPipeline
            ->pipe(new FileRequestModifier())
            ->pipe(new MethodRequestModifier())
            ->pipe(new PlainTextRequestModifier());
    }

    /**
     * If set to false, ignore error "SSL certificate problem: unable to get local issuer certificate"
     * Default to true
     * @param bool $value
     */
    public function setSslVerifyPeer(bool $value)
    {
        $this->defaultCurlOptions[CURLOPT_SSL_VERIFYPEER] = $value;
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

        return new CurlRequest($this, $url, 'GET', null, $headers);
    }

    public function post(string $url, $data = null, $query = null, array $headers = [])
    {
        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        return new CurlRequest($this, $url, 'POST', $data, $headers);
    }

    public function put(string $url, $data = null, $query = null, array $headers = [])
    {
        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        return new CurlRequest($this, $url, 'PUT', $data, $headers);
    }

    public function patch(string $url, $data = null, $query = null, array $headers = [])
    {
        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        return new CurlRequest($this, $url, 'PATCH', $data, $headers);
    }

    public function delete(string $url, $data = null, $query = null, array $headers = [])
    {
        if (!empty($query)) {
            $url .= '?' . (is_string($query) ? $query : http_build_query($query, '', '&'));
        }

        return new CurlRequest($this, $url, 'DELETE', $data, $headers);
    }

    /**
     * @param CurlRequest $request
     * @return resource
     */
    public function prepare(CurlRequest $request)
    {
        $request = $this->requestModifierPipeline->process($request);

        // create curl resource
        $ch = curl_init();

        var_dump($request->getData());
        var_dump($request->getHeaders());

        curl_setopt($ch, CURLOPT_URL, $request->getUrl());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getData());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());

        $curlOptions = $this->defaultCurlOptions->replace($request->getCurlOptions());

        foreach ($curlOptions->all() as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        $headers = $this->defaultHeaders->replace($request->getHeaders());

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers->toHttp());

        return $ch;
    }

    /**
     * @param CurlRequest $request
     * @return CurlResponse
     * @throws CurlError
     */
    public function execute(CurlRequest $request): CurlResponse
    {
        $ch = $this->prepare($request);

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
     * @return RequestModifierPipeline
     */
    public function getRequestModifierPipeline(): RequestModifierPipeline
    {
        return $this->requestModifierPipeline;
    }

}