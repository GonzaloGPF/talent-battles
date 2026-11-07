<?php

namespace App\Objects;

/**
 * Meta tags of a page of the SPA: what search engines and link previews read, since they do not
 * run its JavaScript.
 */
class PageMeta
{
    /**
     * @param  array<int, array<string, mixed>>  $structuredData  schema.org objects, rendered as JSON-LD
     */
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
        public array $structuredData = [],
    ) {}

    /**
     * JSON-LD for a script tag. Escaping < and > keeps a name like "</script>" from closing it.
     */
    public function structuredDataJson(): string
    {
        return json_encode(
            ['@context' => 'https://schema.org', '@graph' => $this->structuredData],
            JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }
}
