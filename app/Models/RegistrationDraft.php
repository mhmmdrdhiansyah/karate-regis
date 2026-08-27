<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'contingent_id',
        'event_id',
        'status',
        'unique_code',
    ];

    protected function casts(): array
    {
        return [
            'unique_code' => 'integer',
        ];
    }

    /**
     * Get the persisted unique payment code for this draft.
     *
     * The code is generated once (random 100-999, unique within the event) and
     * saved, so it stays stable across every render. This keeps the on-page
     * invoice, the PDF, and the amount stored on the payment in agreement —
     * which is what makes the unique-code payment matching work.
     */
    public function getOrAssignUniqueCode(): int
    {
        if ($this->unique_code !== null) {
            return $this->unique_code;
        }

        do {
            $code = random_int(100, 999);
        } while (
            $this->newQuery()
                ->where('event_id', $this->event_id)
                ->where('id', '!=', $this->id)
                ->where('unique_code', $code)
                ->exists()
        );

        $this->unique_code = $code;
        $this->save();

        return $code;
    }

    public function contingent(): BelongsTo
    {
        return $this->belongsTo(Contingent::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RegistrationDraftItem::class);
    }

    public function scopeForManagedEvents($query)
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('panitia')) {
            return $query;
        }

        return $query->whereIn('event_id', $user->managedEvents()->pluck('events.id'));
    }
}
