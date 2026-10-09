<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * Akun user yang terhubung dengan customer ini (dicocokkan via email).
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'email', 'email');
    }
}
