<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoutRun extends Model
{
    // scout_runs has no Laravel-managed timestamps (started_at / finished_at are set explicitly)
    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'country_name',
        'query',
        'urls_found',
        'urls_saved',
        'urls_skipped',
        'started_at',
        'finished_at',
        'notes',
    ];

    protected $casts = [
        'urls_found'   => 'integer',
        'urls_saved'   => 'integer',
        'urls_skipped' => 'integer',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Duration in seconds (null if the run hasn't finished yet).
     */
    public function getDurationSecondsAttribute(): ?int
    {
        if (!$this->finished_at) {
            return null;
        }
        return (int) $this->started_at->diffInSeconds($this->finished_at);
    }

    /**
     * Whether the run completed successfully (finished_at is set).
     */
    public function getIsFinishedAttribute(): bool
    {
        return $this->finished_at !== null;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeFinished($query)
    {
        return $query->whereNotNull('finished_at');
    }

    public function scopeForCountry($query, string $code)
    {
        return $query->whereHas('country', fn ($q) => $q->where('code', strtoupper($code)));
    }
}
