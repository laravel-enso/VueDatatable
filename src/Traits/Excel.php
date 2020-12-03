<?php

namespace LaravelEnso\Tables\Traits;

trait Excel
{
    use TableBuilder {
        export as __invoke;
    }
}
