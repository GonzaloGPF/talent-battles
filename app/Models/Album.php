<?php

namespace App\Models;

use App\Interfaces\HasCommentsInterface;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasDownloadsInterface;
use App\Interfaces\HasFavoritesInterface;
use App\Interfaces\HasFilesInterface;
use App\Interfaces\HasLikesInterface;
use App\Interfaces\HasReportsInterface;
use App\Interfaces\HasTagsInterface;
use App\Traits\Commentable;
use App\Traits\Downloable;
use App\Traits\Favoritable;
use App\Traits\HasCreatedBy;
use App\Traits\HasCreations;
use App\Traits\HasFiles;
use App\Traits\HasReports;
use App\Traits\HasTags;
use App\Traits\IsFilterable;
use App\Traits\Likeable;
use App\Traits\NameFormatter;
use Database\Factories\AlbumFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperAlbum
 */
class Album extends Model implements HasCommentsInterface, HasCreatedByInterface, HasDownloadsInterface, HasFavoritesInterface, HasFilesInterface, HasLikesInterface, HasReportsInterface, HasTagsInterface
{
    /** @use HasFactory<AlbumFactory> */
    use Commentable,
        Downloable,
        Favoritable,
        HasCreatedBy,
        HasCreations,
        HasFactory,
        HasFiles,
        HasReports,
        HasTags,
        IsFilterable,
        Likeable,
        NameFormatter;

    protected $fillable = [
        'name',
        'released',
        'description',
    ];

    protected $casts = [
        'released' => 'date',
    ];

    /**
     * @return HasMany<Design, $this>
     */
    public function designs(): HasMany
    {
        return $this->hasMany(Design::class);
    }

    /**
     * @return HasMany<Track, $this>
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }
}
