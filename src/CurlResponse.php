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
    /**
     * @var string
     */
    private $rawResponse;
    /**
     * @var array
     */
    private $headers;

    public function __construct(string $rawResponse, array $headers)
    {
        $this->rawResponse = $rawResponse;
        $this->headers = $headers;
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

    public function json()
    {
        return json_decode($this->rawResponse, true);
    }
}