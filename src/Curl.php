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
        curl_setopt($ch, CURLOPT_URL, $request->getUri());

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        if($output === false) {
            throw new CurlError();
        }

        return new CurlResponse($output, []);
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