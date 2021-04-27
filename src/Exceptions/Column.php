<?php

namespace LaravelEnso\Tables\Exceptions;

use LaravelEnso\Helpers\Exceptions\EnsoException;
use LaravelEnso\Tables\Attributes\Number;

class Column extends EnsoException
{
    public static function invalidFormat()
    {
        return new static(__(
            'The columns attribute must be an array of objects with at least one element'
        ));
    }

    public static function missingAttributes(string $attrs)
    {
        return new static(__(
            'Mandatory column attribute(s) missing: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function unknownAttributes(string $attrs)
    {
        return new static(__(
            'Unknown Column Attribute(s) Found: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function enumNotFound(string $enum)
    {
        return new static(__(
            'Provided enum does not exist: ":enum"',
            ['enum' => $enum]
        ));
    }

    public static function resourceNotFound(string $resource)
    {
        return new static(__(
            'Provided resource does not exist: ":resource"',
            ['resource' => $resource]
        ));
    }

    public static function invalidTooltip(string $column)
    {
        return new static(__(
            'The tooltip attribute provided for ":column" must be a string',
            ['column' => $column]
        ));
    }

    public static function invalidNumber(string $column)
    {
        return new static(__(
            'Provided number attribute for ":column" must be an object',
            ['column' => $column]
        ));
    }

    public static function invalidClass(string $column)
    {
        return new static(__(
            'The class attribute provided for ":column" must be a string',
            ['column' => $column]
        ));
    }

    public static function invalidAlign(string $column)
    {
        return new static(__(
            'The align attribute provided for ":column" is incorrect',
            ['column' => $column]
        ));
    }

    public static function invalidNumberAttributes(string $column)
    {
        return new static(__(
            'The number configuration provided for ":column" is invalid. Supported :attributes',
            ['column' => $column, 'attributes' => implode(', ', Number::Optional)]
        ));
    }
}
