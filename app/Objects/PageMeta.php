<?php

namespace App\Objects;

/**
 * Meta tags of a page of the SPA: what search engines and link previews read, since they do not
 * run its JavaScript.
 */
class PageMeta
{
    public function __construct(
        public string $title,
        public string $description,
        public string $url,
        public string $image,
        public string $imageType,
        public string $locale,
        public string $type = 'website',
        public bool $indexable = true,
        public ?int $imageWidth = null,
        public ?int $imageHeight = null,
    ) {}
}
