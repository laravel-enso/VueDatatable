<?php

namespace LaravelEnso\Tables\Services\Data;

use Closure;
use Illuminate\Support\Arr;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Data\RequestArgument as Argument;

class FilterAggregator
{
    private const Filters = ['boolean', 'enum', 'select'];
    private const Intervals = ['date', 'number'];

    private Obj $internalFilters;
    private Obj $searches;
    private Obj $filters;
    private Obj $intervals;
    private Obj $params;
    private bool $merged;

    public function __construct($internalFilters, $filters, $intervals, $params)
    {
        $this->internalFilters = new Obj(Argument::parse($internalFilters));
        $this->searches = $this->filter('string');
        $this->filters = new Obj(Argument::parse($filters));
        $this->intervals = new Obj(Argument::parse($intervals));
        $this->params = new Obj(Argument::parse($params));
        $this->merged = false;
    }

    public function searches(): Obj
    {
        $this->mergeIfNeeded();

        return $this->searches;
    }

    public function filters(): Obj
    {
        $this->mergeIfNeeded();

        return $this->filters;
    }

    public function intervals(): Obj
    {
        $this->mergeIfNeeded();

        return $this->intervals;
    }

    public function params(): Obj
    {
        $this->mergeIfNeeded();

        return $this->params;
    }

    public function mergeIfNeeded(): void
    {
        if (! $this->merged) {
            $this->handle();
        }
    }

    public function handle(): void
    {
        $this->extractCustom()
            ->merge($this->filters, self::Filters)
            ->merge($this->filters, self::Intervals, $this->excludeArrays())
            ->merge($this->intervals, self::Intervals, $this->onlyArrays());

        $this->merged = true;
    }

    private function extractCustom(): self
    {
        $this->internalFilters
            ->filter(fn ($filter) => $filter->get('custom'))
            ->each(fn ($filter) => $this->set($this->params, $filter));

        $this->internalFilters = $this->internalFilters
            ->reject(fn ($filter) => $filter->get('custom'));

        return $this;
    }

    private function merge(Obj $filters, array $types, ?Closure $filter = null): self
    {
        $this->filter($types)
            ->when($filter, fn ($filters) => $filters->filter($filter))
            ->each(fn ($filter) => $this->set($filters, $filter));

        return $this;
    }

    private function set(Obj $filters, Obj $filter)
    {
        $array = [];

        Arr::set($array, $filter->get('data'), $filter->get('value'));

        $filters->set(key($array), $filters->has(key($array))
            ? $filters->get(key($array))->merge(new Obj($array[key($array)]))
            : new Obj($array[key($array)]));
    }

    private function filter($types): Obj
    {
        return $this->internalFilters
            ->filter(fn ($filter) => in_array($filter->get('type'), (array) $types));
    }

    private function onlyArrays(): Closure
    {
        return fn ($filter) => $filter->get('value') instanceof Obj;
    }

    private function excludeArrays(): Closure
    {
        return fn ($filter) => ! $filter->get('value') instanceof Obj;
    }
}
