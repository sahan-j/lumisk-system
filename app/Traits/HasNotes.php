<?php

namespace App\Traits;

use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotes
{
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest();
    }

    public function internalNotes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->where('is_internal', true)->latest();
    }

    public function clientNotes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->where('is_internal', false)->latest();
    }
}
