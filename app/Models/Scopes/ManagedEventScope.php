<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ManagedEventScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        // Guest, super-admin, kontingen, role lain → tanpa saringan.
        // Hanya panitia yang dibatasi ke event yang ditugaskan.
        if (! $user || ! $user->hasRole('panitia')) {
            return;
        }

        $builder->whereHas('panitia', fn ($q) => $q->where('users.id', $user->id));
    }
}
