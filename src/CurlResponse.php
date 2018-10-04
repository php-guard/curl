<?php
/**
 * Created by PhpStorm.
 * User: Alexandre
 * Date: 03/10/2018
 * Time: 22:13
 */

namespace PhpGuard\Curl;

class CurlResponse
{
    const JSON_PATTERN = '/^(?:application|text)\/(?:[a-z]+(?:[\.-][0-9a-z]+){0,}[\+\.]|x-)?json(?:-[a-z]+)?/i';
    const XML_PATTERN = '~^(?:text/|application/(?:atom\+|rss\+)?)xml~i';
    /**
     * @var string
     */
    private $rawResponse;
    /**
     * @var array
     */
    private $headers;
    private $lowerHeaders;

    public function __construct(string $rawResponse, array $headers)
    {
        $this->rawResponse = $rawResponse;
        $this->headers = $headers;
        $this->lowerHeaders = array_change_key_case($headers, CASE_LOWER);
    }

    public function raw()
    {
        return $this->rawResponse;
    }

    /**
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function getHeader(string $key) {
        return $this->lowerHeaders[mb_strtolower($key)] ?? null;
    }

    public function json()
    {
        if(!preg_match(self::JSON_PATTERN, $this->getHeader('Content-Type'))) {
            return false;
        }

        return json_decode($this->rawResponse, true);
    }
}