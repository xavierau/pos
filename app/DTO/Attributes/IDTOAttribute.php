<?php

namespace App\DTO\Attributes;

interface IDTOAttribute
{
    /**
     * Process the attribute and return the converted value.
     *
     * @param array $value
     */
    public function process(array $value, bool $isOptional, $defaultValue);
}
