<?php namespace MattyRad\Support\Result;

class Success extends Base
{
    private $response_data;

    public function __construct(array $response_data)
    {
        $this->response_data = $response_data;
    }

    final public function isSuccess()
    {
        return true;
    }

    final public function isFailure()
    {
        return false;
    }

    public function get(string $key)
    {
        return $this->data_get($this->response_data, $key);
    }

    public function toArray(): array
    {
        return $this->response_data;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    // array helpers yanked (and tailored) from
    // https://github.com/illuminate/support/blob/master/helpers.php#L450
    // https://github.com/illuminate/support/blob/master/Arr.php
    private function data_get($target, $key)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if (! is_array($target)) {
                    return null;
                }

                $result = $this->pluck($target, $key);

                return in_array('*', $key) ? $this->collapse($result) : $result;
            }

            if ($this->accessible($target) && $this->exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return null;
            }
        }

        return $target;
    }

    private function pluck($array, $value, $key = null)
    {
        $results = [];

        list($value, $key) = $this->explodePluckParameters($value, $key);

        foreach ($array as $item) {
            $itemValue = $this->data_get($item, $value);

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = $this->data_get($item, $key);

                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    private function explodePluckParameters($value, $key)
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    private function collapse($array)
    {
        $results = [];

        foreach ($array as $values) {
            if (! is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    private function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    private function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }
}
