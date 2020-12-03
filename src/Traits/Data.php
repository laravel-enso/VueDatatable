<?php

namespace LaravelEnso\Tables\Traits;

trait Data
{
    use TableBuilder {
        data as __invoke;
    }
}
