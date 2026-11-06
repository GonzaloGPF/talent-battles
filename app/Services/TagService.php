<?php

namespace App\Services;

use App\Models\Tag;
use App\Rules\NameRule;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TagService
{
    public function saveTags(null|array|string $tags): Collection
    {
        $tags = $this->sanitizeTags($tags);

        if (empty($tags)) {
            return collect();
        }

        return collect($tags)
            ->map(function (string $tag) {
                try {
                    return Tag::firstOrCreate(['name' => $tag], ['name' => $tag]);
                } catch (Exception $e) {
                    return null;
                }
            })
            ->filter();
    }

    public function sanitizeTags(null|array|string $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        if (is_array($tags)) {
            $tags = collect($tags)
                ->map(fn ($tag) => $this->sanitize($tag))
                ->filter()
                ->toArray();
        } else {
            $tags = [$this->sanitize($tags)];
        }

        return collect($tags)
            ->unique()
            ->filter()
            ->values()
            ->toArray();
    }

    public static function sanitize(?string $tag): ?string
    {
        if (! $tag) {
            return null;
        }

        return (string) Str::of($tag)
            ->trim()
            ->lower()
            ->replace(NameRule::FORBIDDEN_CHARS, '');
    }
}
