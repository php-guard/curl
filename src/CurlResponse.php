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
    /**
     * @var int
     */
    private $statusCode;

    public function __construct(int $statusCode, string $rawResponse, Headers $headers)
    {
        $this->statusCode = $statusCode;
        $this->rawResponse = $rawResponse;
        $this->headers = $headers;
    }

    public function raw()
    {
        return $this->rawResponse;
    }

    /**
     * @return Headers
     */
    public function headers(): Headers
    {
        return $this->headers;
    }

    public function json()
    {
        if(!preg_match(self::JSON_PATTERN, $this->headers['Content-Type'])) {
            return false;
        }

        return json_decode($this->rawResponse, true);
    }

    /**
     * @return int
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function isError() :bool {
        return $this->statusCode >= 300;
    }

}