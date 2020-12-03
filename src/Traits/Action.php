<?php

namespace LaravelEnso\Tables\Traits;

trait Action
{
    use TableBuilder {
        action as __invoke;
    }
}
