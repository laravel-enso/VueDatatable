<?php

namespace LaravelEnso\Tables\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Services\Data\Builders\Data as DataBuilder;
use LaravelEnso\Tables\Services\Data\Builders\Meta as MetaBuilder;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\FilterAggregator;
use LaravelEnso\Tables\Services\Data\Request as TableRequest;
use LaravelEnso\Tables\Services\Excel as Service;
use LaravelEnso\Tables\Services\TemplateLoader;

trait TableBuilder
{
    private Config $config;
    private Table $table;
    private TableRequest $tableRequest;

    public function init()
    {
        return (new TemplateLoader($this->table()))
            ->handle()
            ->toArray();
    }

    public function export(Request $request)
    {
        (new Service(
            $request->user(), $this->config(), $this->tableClass
        ))->handle();
    }

    public function data()
    {
        return (new DataBuilder($this->table(), $this->config()))->toArray()
            + (new MetaBuilder($this->table(), $this->config()))->toArray();
    }

    public function action()
    {
        return App::make($this->actionClass, [
            'config' => $this->config(),
            'table' => $this->table(),
        ])->handle();
    }

    private function request(): TableRequest
    {
        return $this->tableRequest
            ??= new TableRequest(
                request('columns'),
                request('meta'),
                $this->filterAggregator()
            );
    }

    private function table(): Table
    {
        return $this->table
            ??= App::make($this->tableClass, ['request' => $this->request()]);
    }

    private function config(): Config
    {
        return $this->config
            ??= new Config(
                $this->request(),
                (new TemplateLoader($this->table()))->handle()
            );
    }

    private function filterAggregator(): FilterAggregator
    {
        return new FilterAggregator(
            request('internalFilters'),
            request('filters'),
            request('intervals'),
            request('params')
        );
    }
}
