<?php

namespace LaravelEnso\Tables\Services\Template\Validators\Columns;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Column as Attributes;
use LaravelEnso\Tables\Attributes\Number;
use LaravelEnso\Tables\Attributes\Style;
use LaravelEnso\Tables\Exceptions\Column as Exception;

class Column
{
    private const Validations = [
        'mandatory', 'optional', 'align', 'class', 'enum',
        'meta', 'number', 'resource', 'tooltip',
    ];

    private Obj $column;

    public function __construct(Obj $column)
    {
        $this->column = $column;
    }

    public function validate(): void
    {
        Collection::wrap(self::Validations)
            ->each(fn ($validation) => $this->{$validation}());
    }

    private function mandatory(): void
    {
        $diff = Collection::wrap(Attributes::Mandatory)
            ->diff($this->column->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::missingAttributes($diff->implode('", "'));
        }
    }

    private function optional(): void
    {
        $attributes = Collection::wrap(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $this->column->keys()->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownAttributes($diff->implode('", "'));
        }
    }

    private function align(): void
    {
        if ($this->invalidAttribute('align', Style::Align)) {
            throw Exception::invalidAlign($this->column->get('name'));
        }
    }

    private function class(): void
    {
        if ($this->invalidString('class')) {
            throw Exception::invalidClass($this->column->get('name'));
        }
    }

    private function enum(): void
    {
        if ($this->missingClass('enum')) {
            throw Exception::enumNotFound($this->column->get('enum'));
        }
    }

    private function meta(): void
    {
        if ($this->column->has('meta')) {
            Meta::validate($this->column);
        }
    }

    private function number(): void
    {
        if ($this->invalidObject('number')) {
            throw Exception::invalidNumber($this->column->get('name'));
        }

        if ($this->invalidAttributes('number', Number::Optional)) {
            throw Exception::invalidNumberAttributes($this->column->get('name'));
        }
    }

    private function resource(): void
    {
        if ($this->missingClass('resource')) {
            throw Exception::resourceNotFound($this->column->get('resource'));
        }
    }

    private function tooltip(): void
    {
        if ($this->invalidString('tooltip')) {
            throw Exception::invalidTooltip($this->column->get('name'));
        }
    }

    private function missingClass(string $attribute): bool
    {
        return $this->column->has($attribute)
            && ! class_exists($this->column->get($attribute));
    }

    private function invalidString(string $attribute): bool
    {
        return $this->column->has($attribute)
            && ! is_string($this->column->get($attribute));
    }

    private function invalidObject(string $attribute): bool //TODO can be aggregated with invalidAttributes
    {
        return $this->column->has($attribute)
            && ! is_object($this->column->get($attribute));
    }

    private function invalidAttribute(string $attribute, array $allowed): bool
    {
        return $this->column->has($attribute)
            && ! in_array($this->column->get($attribute), $allowed);
    }

    private function invalidAttributes(string $attribute, array $allowed): bool
    {
        return $this->column->has($attribute)
            && Collection::wrap($this->column->get($attribute))
            ->keys()->diff($allowed)->isNotEmpty();
    }
}
