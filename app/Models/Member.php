<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    /**
     * Daftar status yang diizinkan: nilai di database => label di tampilan.
     */
    public const STATUSES = [
        'aktif' => 'Aktif',
        'nonaktif' => 'Nonaktif',
    ];

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'status',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}