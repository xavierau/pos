<?php

namespace App\DTO;

use App\Contracts\IRequestDTO;
use App\DTO\Attributes\IDTOAttribute;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionNamedType;

abstract class ADTO implements IRequestDTO
{
    /**
     * Create a new instance from a Laravel Request.
     *
     * @param Request $request
     * @return static
     */
    public static function fromRequest(Request $request): static
    {
        $class = static::class;
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $args = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();
            $isOptional = $parameter->isOptional() || $parameter->allowsNull();

            $value = self::getValueFromRequest($request, $name, $isOptional, $parameter);

            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
                $value = self::convertValue($typeName, $value, $reflection, $name, $isOptional);
            }

            $args[] = $value;
        }

        return new $class(...$args);
    }

    /**
     * Get the value from the request.
     *
     * @param Request $request
     * @param string $name
     * @param bool $isOptional
     * @param \ReflectionParameter $parameter
     * @return mixed
     */
    private static function getValueFromRequest(Request $request, string $name, bool $isOptional, \ReflectionParameter $parameter): mixed
    {
        if ($request->has($name)) {
            return $request->input($name);
        } elseif ($isOptional) {
            return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
        } else {
            throw new \InvalidArgumentException("Missing parameter $name");
        }
    }

    /**
     * Convert the value based on its type.
     *
     * @param string $typeName
     * @param mixed $value
     * @param ReflectionClass $reflection
     * @param string $name
     * @return mixed
     */
    private static function convertValue(string $typeName, mixed $value, ReflectionClass $reflection, string $name, bool $isOptional): mixed
    {


        if (is_null($value)) {
            return $value;
        }

        if (class_exists($typeName) && method_exists($typeName, 'fromRequest')) {
            return $typeName::fromRequest(new Request($value));
        } elseif (enum_exists($typeName)) {
            return $typeName::from($value);
        } elseif (is_array($value)) {
            return self::convertArray($value, $reflection, $name, "Missing or invalid array type annotation for parameter $name", $isOptional);
        } elseif (self::isStandardType($typeName)) {
            settype($value, $typeName);
            return $value;
        } else {
            return self::convertByCaster($value, $reflection, $name, "Invalid type $typeName for parameter $name", $isOptional);
        }
    }


    /**
     * Check if a type is a standard PHP type.
     *
     * @param string $type
     * @return bool
     */
    private static function isStandardType(string $type): bool
    {
        $standardTypes = ['int', 'float', 'string', 'bool', 'array', 'object', 'null'];
        return in_array($type, $standardTypes);
    }

    /**
     * Convert an array of values to an array of DTOs using attributes.
     *
     * @param array $value
     * @param ReflectionClass $reflection
     * @param string $name
     * @return array
     */
    private static function convertArray(array $value, ReflectionClass $reflection, string $name, string $errorMessage, $isOptional): array
    {
        $attribute = collect($reflection->getProperty($name)->getAttributes())
            ->filter(fn($attribute) => (new ReflectionClass($attribute->getName()))->implementsInterface(IDTOAttribute::class))
            ->first();

        if (!$attribute) throw new \InvalidArgumentException($errorMessage);

        return $attribute->newInstance()->process($value, $isOptional, $value);

    }

    private static function convertByCaster($value, ReflectionClass $reflection, string $name, string $errorMessage, bool $isOptional)
    {
        $attribute = collect($reflection->getProperty($name)->getAttributes())
            ->filter(fn($attribute) => (new ReflectionClass($attribute->getName()))->implementsInterface(IDTOAttribute::class))
            ->first();

        if (!$attribute) throw new \InvalidArgumentException($errorMessage);

        return $attribute->newInstance()->process($value, $isOptional, $value);

    }
}
