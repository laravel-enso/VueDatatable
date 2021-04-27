<?php

namespace LaravelEnso\Tables\Traits;

use Illuminate\Http\Request;
use LaravelEnso\Tables\Services\Data\Builders\Data as DataBuilder;
use LaravelEnso\Tables\Services\Data\Builders\Meta as MetaBuilder;

trait Data
{
    use ProvidesData;

    public function __invoke(Request $request)
    {
        ['table' => $table, 'config' => $config] = $this->data($request);

        $data = (new DataBuilder($table, $config))->toArray();

        if ($request->boolean('withMeta', true)) {
            $data += (new MetaBuilder($table, $config))->toArray();
        }

        return $data;
    }
}
