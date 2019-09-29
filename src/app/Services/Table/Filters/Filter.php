<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\app\Classes\Obj;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Request;

class Filter
{
    private $request;
    private $query;
    private $filters;

    public function __construct(Request $request, Builder $query)
    {
        $this->request = $request;
        $this->query = $query;
        $this->filters = false;
    }

    public function handle()
    {
        $this->filter();

        return $this->filters;
    }

    private function filter()
    {
        if (! $this->request->filled('filters')) {
            return $this;
        }

        $this->query->where(function ($query) {
            $this->parse('filters')->each(function ($filters, $table) use ($query) {
                $filters->each(function ($value, $column) use ($table, $query) {
                    if ($this->filterIsValid($value)) {
                        if ($value instanceof Collection) {
                            $value = $value->toArray();
                        }

                        $query->whereIn($table.'.'.$column, (array) $value);
                        $this->filters = true;
                    }
                });
            });
        });

        return $this;
    }

    private function filterIsValid($value)
    {
        return $value !== null
            && $value !== ''
            && ! ($value instanceof Collection && $value->isEmpty());
    }

    private function parse($type)
    {
        return is_string($this->request->get($type))
            ? new Obj(json_decode($this->request->get($type), true))
            : $this->request->get($type);
    }
}