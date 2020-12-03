<?php

namespace LaravelEnso\Tables\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class RouteBuilder
{
    private Collection $actions;
    private string $controller;

    public function __construct(string $controller)
    {
        $this->actions = Collection::wrap(['init', 'data']);
        $this->controller = $controller;
    }

    public function withExport(): self
    {
        $this->actions->push('export');

        return $this;
    }

    public function withAction(): self
    {
        $this->actions->push('action');

        return $this;
    }

    public function build()
    {
        return $this->actions->each(fn ($action) => $this->$action());
    }

    private function init()
    {
        return Route::get('initTable', [$this->controller, 'init'])->name('initTable');
    }

    private function data()
    {
        return Route::get('tableData', [$this->controller, 'data'])->name('tableData');
    }

    private function export()
    {
        return Route::get('exportExcel', [$this->controller, 'export'])->name('exportExcel');
    }

    private function action()
    {
        return Route::get('tableAction', [$this->controller, 'action'])->name('tableAction');
    }
}
