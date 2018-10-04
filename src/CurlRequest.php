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
     * @return CurlResponse
     * @throws CurlError
     */
    public function execute(): CurlResponse {
        return $this->curl->execute($this);
    }
}