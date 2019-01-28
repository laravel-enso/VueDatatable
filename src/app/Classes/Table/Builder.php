<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

use LaravelEnso\Helpers\app\Classes\Obj;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use LaravelEnso\VueDatatable\app\Exceptions\QueryException;
use LaravelEnso\VueDatatable\app\Classes\Table\Computors\Date;
use LaravelEnso\VueDatatable\app\Classes\Table\Computors\Enum;
use LaravelEnso\VueDatatable\app\Classes\Table\Computors\Translatable;

class Builder
{
    private $request;
    private $query;
    private $count;
    private $filtered;
    private $total;
    private $data;
    private $columns;
    private $meta;
    private $fullRecordInfo;
    private $statics;
    private $fetchMode;

    public function __construct(Obj $request, QueryBuilder $query)
    {
        $this->request = $request;
        $this->computeMeta();
        $this->meta = $this->request->get('meta');
        $this->computeColumns();
        $this->columns = $this->request->get('columns');
        $this->query = $query;
        $this->total = collect();
        $this->statics = false;
        $this->fetchMode = false;
    }

    public function fetcher()
    {
        $this->meta->set(
            'length', config('enso.datatable.export.chunk')
        );

        return $this;
    }

    public function fetch($page = 0)
    {
        $this->fetchMode = true;

        $this->meta->set(
            'start', $this->meta->get('length') * $page
        );

        $this->run();

        return $this->data;
    }

    public function data()
    {
        $this->run();

        $this->checkActions();

        return [
            'count' => $this->count,
            'filtered' => $this->filtered,
            'total' => $this->total,
            'data' => $this->data,
            'fullRecordInfo' => $this->fullRecordInfo,
            'filters' => $this->hasFilters(),
        ];
    }

    private function run()
    {
        $this->initStatics()
            ->setCount()
            ->setDetailedInfo()
            ->filter()
            ->sort()
            ->setTotal()
            ->limit()
            ->setData();

        if ($this->data->isNotEmpty()) {
            $this->setAppends()
            ->collect()
            ->computeEnum()
            ->computeDate()
            ->computeTranslatable()
            ->flatten();
        }
    }

    private function checkActions()
    {
        if (count($this->data) === 0) {
            return;
        }

        if (! isset($this->data[0]['dtRowId'])) {
            throw new QueryException(__(
                'You have to add in the main query \'id as "dtRowId"\' for the actions to work'
            ));
        }
    }

    private function count()
    {
        return $this->query->count();
    }

    private function setCount()
    {
        if (! $this->fetchMode) {
            $this->filtered = $this->count = $this->count();
        }

        return $this;
    }

    private function setDetailedInfo()
    {
        $this->fullRecordInfo = $this->meta->get('forceInfo')
            || (! $this->fetchMode && (! $this->hasFilters()
                || $this->count <= config('enso.datatable.fullInfoRecordLimit')));

        return $this;
    }

    private function filter()
    {
        if ($this->hasFilters()) {
            (new Filters($this->request, $this->query, $this->columns))->set();

            if ($this->fullRecordInfo) {
                $this->filtered = $this->count();
            }
        }

        return $this;
    }

    private function sort()
    {
        if (! $this->meta->get('sort')) {
            return $this;
        }

        $this->columns->each(function ($column) {
            if ($column->get('meta')->get('sortable') && $column->get('meta')->get('sort')) {
                $column->get('meta')->get('nullLast')
                    ? $this->query->orderByRaw($this->rawSort($column))
                    : $this->query->orderBy(
                        $column->get('data'), $column->get('meta')->get('sort')
                    );
            }
        });

        return $this;
    }

    private function rawSort($column)
    {
        return "ISNULL({$column->get('data')}),"
            ."{$column->get('data')} {$column->get('meta')->get('sort')}";
    }

    private function setTotal()
    {
        if (! $this->meta->get('total') || ! $this->fullRecordInfo || $this->fetchMode) {
            return $this;
        }

        $this->total = $this->columns
            ->reduce(function ($total, $column) {
                if ($column->get('meta')->get('total')) {
                    $total[$column->get('name')] = $this->query->sum($column->get('data'));
                }

                return $total;
            }, []);

        return $this;
    }

    private function limit()
    {
        $this->query->skip($this->meta->get('start'))
            ->take($this->meta->get('length'));

        return $this;
    }

    private function setData()
    {
        $this->data = $this->query->get();

        return $this;
    }

    private function setAppends()
    {
        if (! $this->request->has('appends')) {
            return $this;
        }

        $this->data->each->setAppends(
            $this->request->get('appends')
        );

        return $this;
    }

    private function collect()
    {
        $this->data = collect($this->data->toArray());

        return $this;
    }

    private function initStatics()
    {
        if ($this->statics) {
            return $this;
        }

        if ($this->meta->get('enum')) {
            Enum::columns($this->columns);
        }

        if ($this->meta->get('date')) {
            Date::columns($this->columns);
        }

        if ($this->fetchMode && $this->meta->get('translatable')) {
            Translatable::columns($this->columns);
        }

        $this->statics = true;

        return $this;
    }

    private function computeEnum()
    {
        if ($this->meta->get('enum')) {
            $this->data = $this->data->map(function ($row) {
                return Enum::compute($row, $this->columns);
            });
        }

        return $this;
    }

    private function computeDate()
    {
        if ($this->meta->get('date')) {
            $this->data = $this->data->map(function ($row) {
                return Date::compute($row, $this->columns);
            });
        }

        return $this;
    }

    private function computeTranslatable()
    {
        if ($this->fetchMode && $this->meta->get('translatable')) {
            $this->data = $this->data->map(function ($row) {
                return Translatable::compute($row);
            });
        }

        return $this;
    }

    private function flatten()
    {
        $this->data = collect($this->data)
            ->map(function ($record) {
                return array_dot($record);
            });
    }

    private function computeMeta()
    {
        $this->request->set(
            'meta',
            new Obj($this->array($this->request->get('meta')))
        );
    }

    private function computeColumns()
    {
        $this->request->set(
            'columns',
            collect($this->request->get('columns'))
                ->map(function ($column) {
                    return new Obj($this->array($column));
                })
        );
    }

    private function array($arg)
    {
        return is_string($arg)
            ? json_decode($arg, true)
            : $arg;
    }

    private function hasFilters()
    {
        return $this->request->filled('search')
            || $this->request->has('filters')
            || $this->request->has('intervals');
    }
}
