<?php
/**
 * php-guard/curl <https://github.com/php-guard/curl>
 * Copyright (C) ${YEAR} by Alexandre Le Borgne <alexandre.leborgne.83@gmail.com>.
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

    public function __construct(array $headers = [])
    {
        $this->headers = self::normaliseHeaders($headers);
    }

    /**
     * Whether a offset exists.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned
     *
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->headers[self::normalizeHeaderKey($offset)]);
    }

    /**
     * Offset to retrieve.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed can return all value types
     *
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->headers[self::normalizeHeaderKey($offset)] ?? null;
    }

    /**
     * Offset to set.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->headers[self::normalizeHeaderKey($offset)] = $value;
    }

    /**
     * Offset to unset.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->headers[self::normalizeHeaderKey($offset)]);
    }

    public function all()
    {
        return $this->headers;
    }

    public function replace(array $headers)
    {
        return new self(array_replace($this->headers, self::normaliseHeaders($headers)));
    }

    public function toHttp(): array
    {
        return array_map(function ($k, $v) {
            return $k.':'.$v;
        }, array_keys($this->headers), $this->headers);
    }

    public static function normalizeHeaderKey(string $key)
    {
        return ucwords($key, '-');
    }

    public static function normaliseHeaders(array $headers)
    {
        return self::array_map_assoc(function ($k, $v) {
            return [self::normalizeHeaderKey($k) => $v];
        }, $headers);
    }

    public static function array_map_assoc(callable $f, array $a)
    {
        return array_reduce(array_map($f, array_keys($a), $a), function (array $acc, array $a) {
            return $acc + $a;
        }, []);
    }
}
