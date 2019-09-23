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

    public function getPhotoAttribute()
    {
        if ($this->photos()->count() > 0) {
            return $this->photos()->first()->link;
        }
        return Photo::wallet();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }


    public function getAccountBalanceAttribute()
    {
        return $this->transactions()->balance();
    }

    public function photos()
    {
        return $this->morphMany(Photo::class, "profile");
    }
}
