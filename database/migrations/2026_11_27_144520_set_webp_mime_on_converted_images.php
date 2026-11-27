<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Uploaded images are converted to WebP, but they kept the mime they were uploaded with (image/avif, image/png...).
 * The file route and the og:image:type tag announced a format that did not match the content. Every image stored as
 * .webp gets the WebP mime. Files that are not images are left as they are.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('files')
            ->where('path', 'like', '%.webp')
            ->where('mime', 'like', 'image/%')
            ->where('mime', '!=', 'image/webp')
            ->update(['mime' => 'image/webp']);
    }

    /**
     * The mimes the images were uploaded with were not kept, and they did not match the stored files anyway.
     */
    public function down(): void
    {
        //
    }
};
