<?php namespace MattyRad\Support;

use ReflectionClass;
use ReflectionParameter;
use InvalidArgumentException;

trait Conformation
{
    private static $gettype_to_typehint_map = [
        'boolean' => 'bool',
        'integer' => 'int',
        'double' => 'float',
        'string' => 'string',
        'array' => 'array',
        'object' => 'object',
        'resource' => 'resource',
        'resource (closed)' => 'resource (closed)',
        'NULL' => 'NULL',
        'unknown type' => 'unknown type',
    ];

    public static function fromArray(array $arr)
    {
        $parts = static::filterAndOrderSource($arr);

        return new self(...$parts);
    }

    private static function filterAndOrderSource(array $source)
    {
        $constructor_params = static::getConstructorParameterNames();

        // fill optional parameters with their default value, if they aren't provided
        $filled = static::fillOptionalParams($source);

        // filter out any additional keys in the source array
        $filtered = static::arrayOnly($filled, $constructor_params);

        // sort the keys in the same order as the constructor params
        $ordered = static::sortOrder($constructor_params, $filled);

        if (array_count_values(array_keys($filtered)) != array_count_values($constructor_params)) {
            $missing_keys = array_diff($constructor_params, array_keys($filtered));

            throw new InvalidArgumentException(static::selfName() . ' missing key(s): ' . implode(', ', $missing_keys));
        }

        // in some cases, php will actually cast a value to its typehint, so make sure that are types are correct
        $casted_and_validated = static::castConformationsAndValidateTypes($ordered);

        return array_values($casted_and_validated);
    }

    private static function getConstructorParameterNames()
    {
        $params = (new ReflectionClass(__CLASS__))->getConstructor()->getParameters();

        return array_map(function (ReflectionParameter $param) {
            return $param->name;
        }, $params);
    }

    private static function getOptionalConstructorParameters()
    {
        $params = (new ReflectionClass(__CLASS__))->getConstructor()->getParameters();

        return array_filter($params, function (ReflectionParameter $param) {
            return $param->isOptional();
        });
    }

    private static function fillOptionalParams(array $source)
    {
        $optional_params = static::getOptionalConstructorParameters();

        foreach ($optional_params as $optional_param) {
            if (! array_key_exists($optional_param->getName(), $source)) {
                $source[$optional_param->getName()] = $optional_param->getDefaultValue();
            }
        }

        return $source;
    }

    private static function castConformationsAndValidateTypes(array $ordered)
    {
        $params = (new ReflectionClass(__CLASS__))->getConstructor()->getParameters();

        foreach ($params as $param) {
            if (! $param->hasType()) {
                continue;
            }

            $value = $ordered[$param->name];

            if ($param->getType()->allowsNull() && is_null($value)) {
                continue;
            }

            $typehint = (string) $param->getType();

            $local_type = is_object($value) ? get_class($value) : static::$gettype_to_typehint_map[gettype($value)];

            try {
                $trait_names = array_keys((new ReflectionClass($typehint))->getTraits());
            } catch (\ReflectionException $e) {
                $trait_names = [];
            }

            if (in_array(Conformation::class, $trait_names) && $local_type === 'array') {
                // put the array data into the Conformation parameter
                $ordered[$param->name] = $typehint::fromArray($value);
            }

            if ($typehint !== $local_type && ! (in_array(Conformation::class, $trait_names) && $local_type === 'array')) {
                $message = 'Param "' . $param->name . '" expected type ' . $param->getType();
                $message .= ' but got type ' . $local_type;
                $message .= is_object($value) ? '' : " with value '$value'";
                throw new InvalidArgumentException($message);
            }

            if (property_exists(__CLASS__, 'array_conformations') && $local_type === 'array') {
                foreach (static::$array_conformations as $array_param_name => $conformation_class) {
                    if ($param->name !== $array_param_name) {
                        continue;
                    }
                    $casted_conformations = [];

                    foreach ($value as $k => $v) {
                        $casted_conformations[] = $conformation_class::fromArray($v);
                    }

                    $ordered[$array_param_name] = $casted_conformations;
                }
            }
        }

        return $ordered;
    }

    private static function arrayOnly(array $from, array $to)
    {
        return array_intersect_key($from, array_flip((array) $to));
    }

    private static function sortOrder(array $keys_to_conform_to, array $from)
    {
        return array_merge(array_flip($keys_to_conform_to), $from);
    }

    private static function selfName()
    {
        return (new ReflectionClass(__CLASS__))->getShortName();
    }
}
