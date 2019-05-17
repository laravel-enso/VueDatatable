<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Http\Request;

trait Action
{
    public function action(Request $request)
    {
        (new $this->actionClass(
            $this->tableClass, $request->all()
        ))->run();
    }
}
