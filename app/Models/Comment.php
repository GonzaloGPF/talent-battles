<?php

namespace App\Models;

use App\Interfaces\FilterableMorph;
use App\Interfaces\HasCommentsInterface;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasFilesInterface;
use App\Interfaces\HasLikesInterface;
use App\Interfaces\HasReportsInterface;
use App\Traits\HasCreatedBy;
use App\Traits\HasFiles;
use App\Traits\HasReports;
use App\Traits\IsFilterableMorph;
use App\Traits\Likeable;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperComment
 */
class Comment extends Model implements FilterableMorph, HasCommentsInterface, HasCreatedByInterface, HasFilesInterface, HasLikesInterface, HasReportsInterface
{
    use Filterable,
        HasCreatedBy,
        HasFactory,
        HasFiles,
        HasReports,
        IsFilterableMorph,
        Likeable;

    protected $fillable = [
        'comment_id',
        'content',
    ];

    protected $casts = [
        'comment_id' => 'int',
    ];

    public static function getMorphColumn(): string
    {
        return 'commentable';
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getTargetUser(): ?User
    {
        return $this->commentable->createdBy;
    }

    protected function title(): Attribute
    {
        return Attribute::set(fn ($value) => $value ? Str::ucfirst(trim($value)) : null);
    }

    public function getContentAttribute(string $value): string
    {
        return Str::ucfirst($value);
    }

    /**
     * Override.
     * It calculates what directory uses to save files using morphed model name, in plural.
     * For example, if its a Comment of a Client, the file directory will be comments/clients
     */
    public function getFilesDirectory(): string
    {
        $modelName = class_basename($this->commentable);

        return 'comments/'.Str::plural(Str::lower($modelName));
    }
}
