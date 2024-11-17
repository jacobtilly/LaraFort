<?php

namespace JacobTilly\LaraFort\Models;

use Illuminate\Database\Eloquent\Model;

class FortnoxCredential extends Model
{
    protected $table = 'fortnox_credentials';
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}

