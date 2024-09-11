<?php

namespace App\DTO\Attributes;

use App\Contracts\IRequestDTO;
use Attribute;
use Illuminate\Http\Request;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayOf implements IDTOAttribute
{
    public function __construct(public string $type)
    {
    }

    public function process(array $value, bool $isOptional, $defaultValue): array
    {
        $interfaces = class_implements($this->type);
        if (in_array(IRequestDTO::class, $interfaces)) {
            return array_map(fn($item) => $this->type::fromRequest(new Request($item)), $value);
        } elseif
        (in_array(IDTOAttribute::class, $interfaces)) {
            return array_map(fn($item) => (new ($this->type))->process($item, $isOptional, $defaultValue));
        }
        throw new \Exception('Error in ArrayOf attribute, ', $value);
    }
}
