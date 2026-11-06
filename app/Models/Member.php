<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}