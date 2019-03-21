# curl

[![Build Status](https://scrutinizer-ci.com/g/php-guard/curl/badges/build.png?b=master)](https://scrutinizer-ci.com/g/php-guard/curl/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/php-guard/curl/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/php-guard/curl/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/php-guard/curl/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/php-guard/curl/?branch=master)
[![GPL Licence](https://badges.frapsoft.com/os/gpl/gpl.png?v=103)](https://opensource.org/licenses/GPL-3.0/)


This library is an alternative to "https://github.com/php-curl-class/php-curl-class". 
Easy use with CurlRequest and CurlResponse objects.

### Installation

Install via [Composer](https://getcomposer.org/)

    $ composer require php-guard/curl
    
### Requirements

 -   php: ^7.1


### Usage


##### Quick Start

    require __DIR__ . '/vendor/autoload.php';
    
    use \PhpGuard\Curl\Curl;
	use \PhpGuard\Curl\CurlError;
    
    $this->curl  =  new  Curl();
    	
	try {
		// Execute a single request
		$response = $this->curl->get('http://example.com'); // Return a CurlResponse
		echo $response->raw();

		// Execute multiple requests
		$responses = $this->curl->multi() // Create a MultiCurl object
			->get('http://example.com')   // Add a get request
			->post('http://example.com')  // Add a post request
			->execute();                  // Return an array of CurlResponse

		foreach ($responses as $response) {
			echo $response->raw();
		}
	} catch (CurlError $e) {
		echo 'Error: ' . $curl->getCode(). ': ' . $curl->getMessage(). "\n";
	}

##### Available Methods

**\PhpGuard\Curl\Curl**

HTTP methods

    get(string $url, $query = null, array $headers = []): CurlResponse
    post(string $url, $data = null, $query = null, array $headers = []): CurlResponse
    put(string $url, $data = null, $query = null, array $headers = []): CurlResponse
    patch(string $url, $data = null, $query = null, array $headers = []): CurlResponse
    delete(string $url, $data = null, $query = null, array $headers = []): CurlResponse
    
Run a single request
    
    execute(CurlRequest  $request): CurlResponse

Prepare multiple requests

    multi(array  $options  = []): MultiCurl
    
Edit request and response processing (advanced usage)

    getCurlRequestFactory(): CurlRequestFactory
    getRequestModifierPipeline(): RequestModifierPipeline


**\PhpGuard\Curl\CurlRequest**

You can use Curl to execute requests or you can use 
the CurlRequestFactory to return CurlRequest instances.

Properties url, method and data have getters and setters.

The method `setHeaderContentType(string $contentType)` is a shortcut for 
 
    $curlRequest->getHeaders['Content-Type'] = $contentType
 
Other methods

    execute(bool $throwExceptionOnHttpError = false): CurlResponse
    getCurlOptions(): CurlOptions
    getHeaders(): Headers

**\PhpGuard\Curl\MultiCurl**

    get(string $url, $query = null, array $headers = []): self
    post(string $url, $data = null, $query = null, array $headers = []): self
    put(string $url, $data = null, $query = null, array $headers = []): self
    patch(string $url, $data = null, $query = null, array $headers = []): self
    delete(string $url, $data = null, $query = null, array $headers = []): self
    execute(): CurlResponse[]

**\PhpGuard\Curl\CurlResponse**

    statusCode(): int
    isError(): bool // True if status code >= 300
    headers(): Headers
    raw() // Raw content of the response
    json() // Array or false if not a json response
    
**\PhpGuard\Curl\CurlRequestFactory**

Set the base url for all requests

     setBaseUrl(?string $baseUrl): self
     
Set the option curl CURLOPT_SSL_VERIFYPEER     
     
     setSslVerifyPeer(bool $value): self
     
Create a CurlRequest

     create(string $method, string $url, $data = null, $query = null, array $headers = []): CurlRequest
     
Edit other default curl options
     
     getDefaultCurlOptions(): CurlOptions
     getDefaultHeaders(): Headers

**\PhpGuard\Curl\Collection\CurlOptions**

This class implements `\ArrayAccess`. It can therefore be used as an array.

**\PhpGuard\Curl\Collection\Headers**

This class implements `\ArrayAccess`. It can therefore be used as an array.

In addition, the keys are case insensitive.

**\PhpGuard\Curl\RequestModifierPipeline**

Add an object to modify CURL requests

    pipe(RequestModifierInterface $requestModifier): self
    
By default, FileRequestModifier and PlainTextRequestModifier are active.
If necessary, you can add an instance of ProxyRequestModifier

* ProxyRequestModifier allows you to define curl options to use a proxy

* FileRequestModifier is used to manage file paths starting with @ 
and passed as a parameter by transforming them into CurlFile 
and then modifying the HTTP Content-Type header.

* PlainTextRequestModifier changes the HTTP Content-Type header 
to text/plain when a string is passed as a parameter.
