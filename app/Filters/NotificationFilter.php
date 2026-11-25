<?php

namespace App\Filters;

class NotificationFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [];

    public function read(mixed $value): void
    {
        $value ?
            $this->whereNotNull('read_at') :
            $this->whereNull('read_at');
    }

    public function type(mixed $type): void
    {
        $this->where('type', 'like', "%$type%");
    }

    public function messageKeys(mixed $value): void
    {
        $this->where('data->message_keys', 'like', "%$value%");
    }

    public function level(mixed $level): void
    {
        $this->where('data->level', 'like', $level);
    }

    public function isRead(mixed $value): void
    {
        $this->nullable('read_at', $value);
    }

    public function noty(mixed $value): void
    {
        $this->where('data->noty_id', $value);
    }
}
