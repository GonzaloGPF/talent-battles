<?php

namespace App\Traits;

use App\Enums\TrackTypes;
use App\Models\Design;
use App\Models\Track;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCreations
{
    public function designs(): HasMany
    {
        return $this->hasMany(Design::class, 'created_by');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class, 'created_by');
    }

    public function beats(): HasMany
    {
        return $this->tracks()
            ->where('track_type', TrackTypes::BEAT);
    }

    public function samples(): HasMany
    {
        return $this->tracks()
            ->where('track_type', TrackTypes::SAMPLE);
    }

    public function songs(): HasMany
    {
        return $this->tracks()
            ->where('track_type', TrackTypes::SONG);
    }
}
