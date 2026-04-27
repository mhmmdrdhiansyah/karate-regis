<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contingent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'official_name',
        'phone',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
