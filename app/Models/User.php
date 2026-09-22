<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'firebase_uid',
        'role',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Manager-only for now; kept as a column so coach/athlete can be added later.
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }
}
