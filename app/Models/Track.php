<?php

namespace App\Models;

use App\Enums\Keys;
use App\Enums\Thematics;
use App\Enums\TrackTypes;
use App\Interfaces\HasBattlesInterface;
use App\Interfaces\HasCommentsInterface;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasDownloadsInterface;
use App\Interfaces\HasFavoritesInterface;
use App\Interfaces\HasFilesInterface;
use App\Interfaces\HasLikesInterface;
use App\Interfaces\HasOrderLineInterface;
use App\Interfaces\HasProductInterface;
use App\Interfaces\HasReportsInterface;
use App\Interfaces\HasTagsInterface;
use App\Interfaces\HasVotesInterface;
use App\Interfaces\PromotableInterface;
use App\Traits\Commentable;
use App\Traits\Downloable;
use App\Traits\Favoritable;
use App\Traits\HasBattles;
use App\Traits\HasCreatedBy;
use App\Traits\HasFiles;
use App\Traits\HasOrderLine;
use App\Traits\HasProduct;
use App\Traits\HasReports;
use App\Traits\HasTags;
use App\Traits\HasVotes;
use App\Traits\IsFilterable;
use App\Traits\Likeable;
use App\Traits\NameFormatter;
use App\Traits\Promotable;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTrack
 */
class Track extends Model implements HasBattlesInterface, HasCommentsInterface, HasCreatedByInterface, HasDownloadsInterface, HasFavoritesInterface, HasFilesInterface, HasLikesInterface, HasOrderLineInterface, HasProductInterface, HasReportsInterface, HasTagsInterface, HasVotesInterface, PromotableInterface
{
    use BroadcastsEvents,
        Commentable,
        Downloable,
        Favoritable,
        HasBattles,
        HasCreatedBy,
        HasFactory,
        HasFiles,
        HasOrderLine,
        HasProduct,
        HasReports,
        HasTags,
        HasVotes,
        IsFilterable,
        Likeable,
        NameFormatter,
        Promotable;

    const DEFAULT_COLOR = '#0D47A1';

    protected $fillable = [
        'name',
        'album_id',
        'track_type',
        'thematic',
        'key',
        'instruments',
        'genres',
        'aspect_ratio',
        'bpm',
        'color',
        'description',
        'can_challenge',
    ];

    protected $hidden = [
        'waveform',
    ];

    public static array $FORMATS = [
        'aac',
        'amr',
        'flac',
        'mp3',
        'ogg',
        'wav',
        'wma',
    ];

    protected $casts = [
        'album_id' => 'int',
        'track_type' => TrackTypes::class,
        'thematic' => Thematics::class,
        'key' => Keys::class,
        'instruments' => AsCollection::class,
        'genres' => AsCollection::class,
        'duration' => 'float',
        'bpm' => 'float',
        'waveform' => 'array',
        'can_challenge' => 'bool',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function broadcastOn($event): array
    {
        return [$this, $this->createdBy];
    }
}
