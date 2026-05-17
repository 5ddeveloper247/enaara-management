<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftRosterEntryEvent extends Model
{
    public const EVENT_CREATED = 'created';

    public const EVENT_UPDATED = 'updated';

    public const EVENT_DELETED = 'deleted';

    protected $fillable = [
        'shift_roster_entry_id',
        'event',
        'user_id',
        'event_at',
        'summary',
        'changes',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'changes' => 'array',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ShiftRosterEntry::class, 'shift_roster_entry_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
