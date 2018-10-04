<?php
/**
 * Created by PhpStorm.
 * User: Alexandre
 * Date: 04/10/2018
 * Time: 21:39
 */

namespace PhpGuard\Curl;


class Headers implements \ArrayAccess
{
    const CONTENT_TYPE_TEXT_PLAIN = 'text/plain';
    const CONTENT_TYPE_MULTIPART_FORM_DATA = 'multipart/form-data';
    const CONTENT_TYPE_FORM_URL_ENCODED = 'application/x-www-form-urlencoded';
    const CONTENT_TYPE_FORM_JSON = 'application/json';

    const CONTENT_TYPE_PATTERN_JSON = '/^(?:application|text)\/(?:[a-z]+(?:[\.-][0-9a-z]+){0,}[\+\.]|x-)?json(?:-[a-z]+)?/i';

    const CONTENT_TYPE = 'Content-Type';

    /**
     * @var array
     */
    private $headers;
    /**
     * @var array
     */
    private $lowerHeaders;

    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
        $this->lowerHeaders = array_change_key_case($headers, CASE_LOWER);
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
       return isset($this->lowerHeaders[strtolower($offset)]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->lowerHeaders[strtolower($offset)] ?? null;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->headers[$offset] = $value;
        $this->lowerHeaders[strtolower($offset)] = $value;
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->headers[$offset]);
        unset($this->lowerHeaders[strtolower($offset)]);
    }

    public function replace(array $headers)
    {
        return new self(array_replace($this->headers, $headers));
    }

    public function toHttp(): array {
        return array_map(function ($k, $v) {
            return $k . ':' . $v;
        }, array_keys($this->headers), $this->headers);
    }
}