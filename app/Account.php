<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = ['id'];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
