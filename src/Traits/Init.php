<?php

namespace LaravelEnso\Tables\Traits;

trait Init
{
    use TableBuilder {
        init as __invoke;
    }
}
