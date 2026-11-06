<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class NotificationFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    protected function read($value): Builder
    {
        $value ?
            $this->whereNotNull('read_at') :
            $this->whereNull('read_at');

        return $this->query;
    }

    protected function type($type): Builder
    {
        $this->where('type', 'like', "%$type%");

        return $this->query;
    }

    protected function messageKeys($value): Builder
    {
        $this->where('data->message_keys', 'like', "%$value%");

        return $this->query;
    }

    protected function level($level): Builder
    {
        $this->where('data->level', 'like', $level);

        return $this->query;
    }

    protected function isRead($value): Builder
    {
        $this->nullable('read_at', $value);

        return $this->query;
    }

    protected function noty($value): Builder
    {
        $this->where('data->noty_id', $value);

        return $this->query;
    }
}
