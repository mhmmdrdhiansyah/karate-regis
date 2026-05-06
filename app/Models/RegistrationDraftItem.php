<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationDraftItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_draft_id',
        'participant_id',
        'sub_category_id',
        'team_group_id',
    ];

    public function draft(): BelongsTo
    {
        return $this->belongsTo(RegistrationDraft::class, 'registration_draft_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function teamGroup(): BelongsTo
    {
        return $this->belongsTo(TeamGroup::class);
    }
}
