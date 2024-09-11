<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface IRequestDTO
{
    public static function fromRequest(Request $request): static;
}
