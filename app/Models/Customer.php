<?php

namespace App\Models;
use App\Models\Order;
use App\Models\Review;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}