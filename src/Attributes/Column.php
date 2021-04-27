<?php

namespace LaravelEnso\Tables\Attributes;

class Column
{
    public const Mandatory = ['data', 'label', 'name'];

    public const Optional = [
        'align', 'class', 'dateFormat', 'enum', 'meta', 'number', 'tooltip', 'resource',
    ];

    public const Meta = [
        'average', 'boolean', 'clickable', 'cents', 'customTotal', 'date', 'datetime',
        'filterable', 'icon', 'notExportable', 'nullLast', 'searchable', 'rawTotal',
        'rogue', 'slot', 'sortable', 'sort:ASC', 'sort:DESC', 'translatable', 'total',
        'notVisible',
    ];
}
