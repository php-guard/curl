<?php
/**
 * Created by PhpStorm.
 * User: Alexandre
 * Date: 03/10/2018
 * Time: 22:12
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

    public function __construct()
    {
    }

    public function setCurlOption(int $key, $value)
    {
        $this->curlOptions[$key] = $value;
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

    public function get(string $uri, ?array $data = null)
    {
        return new CurlRequest($this, $uri, 'GET', $data);
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

        if (!empty($data) && in_array($request->getMethod(), ['GET', 'HEAD'])) {
            $url .= '?' . (is_string($data) ? $data : http_build_query($data, '', '&'));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());

        $curlOptions = array_replace(self::DEFAULT_CURL_OPTIONS, $this->curlOptions);
        foreach ($curlOptions as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        // $output contains the output string
        $output = curl_exec($ch);

        if ($output === false) {
            $message = curl_error($ch);
            $code = curl_errno($ch);

            // close curl resource to free up system resources
            curl_close($ch);
            throw new CurlError($message, $code);
        }

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

        return new CurlResponse($raw, $headers);
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