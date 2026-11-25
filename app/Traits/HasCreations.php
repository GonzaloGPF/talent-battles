<?php

namespace App\Traits;

use App\Enums\TrackTypes;
use App\Models\Design;
use App\Models\Track;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCreations
{
    /**
     * @return HasMany<Design, $this>
     */
    public function designs(): HasMany
    {
        return $this->hasMany(Design::class, 'created_by');
    }

    /**
     * @return HasMany<Track, $this>
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class, 'created_by');
    }

    /**
     * @return HasMany<Track, $this>
     */
    public function beats(): HasMany
    {
        return $this->tracks()
            ->where('track_type', TrackTypes::BEAT);
    }

    /**
     * @return HasMany<Track, $this>
     */
    public function samples(): HasMany
    {
        return $this->tracks()
            ->where('track_type', TrackTypes::SAMPLE);
    }

    /**
     * @return HasMany<Track, $this>
     */
    public function songs(): HasMany
    {
        return $this->tracks()
            ->where('track_type', TrackTypes::SONG);
    }
}
