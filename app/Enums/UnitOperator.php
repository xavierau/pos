<?php

namespace App\Enums;

enum UnitOperator: string
{
    use EnumsToArray;

    const MULTIPLY = '*';
    const DIVIDE = '/';
}
