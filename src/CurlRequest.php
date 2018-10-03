<?php
/**
 * Created by PhpStorm.
 * User: Alexandre
 * Date: 03/10/2018
 * Time: 22:13
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
    protected $uri;

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var ?array
     */
    protected $data;

    /**
     * @var array
     */
    protected $headers = [];

    public function __construct(Curl $curl, string $uri, string $method = 'GET', ?array $data = null, ?array $headers = [])
    {
        $this->curl = $curl;
        $this->uri = $uri;
        $this->method = $method;
        $this->data = $data;
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
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
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function addHeader($key, $value) {
        $this->headers[$key] = $value;
    }

    public function execute() {
        $this->curl->execute($this);
    }
}