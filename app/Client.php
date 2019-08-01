<?php

namespace App;

use App\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    public $role = 'client';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'country_code', 'notes', 'club', 'language', 'phone_number','status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getPhotoAttribute()
    {
        if ($this->photos()->count() > 0) {
            return $this->photos()->first()->link;
        }
        return Photo::avatar();
    }

    public function getPhoneNumberAttribute($value)
    {
        $phone = phone($value, $this->country_code);
        return $phone->formatE164();
    }

    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = phone($value, $this->country_code)->formatE164();
    }

    public function getAccountBalanceAttribute()
    {
        return $this->accounts()->sum('balance');
    }

    public function getPhoneAttribute()
    {
        try {
            $phone = phone($this->attributes['phone_number'], $this->country_code);
            return $phone->formatInternational();
        } catch (\Exception $e) {
            return "";
        }
    }

    public function photos()
    {
        return $this->morphMany(Photo::class, "profile");
    }

    public function scopeByAdminRole(Builder $query)
    {
        return user()->club == '*' ? $query : $query->where('club', 'regexp', sprintf("^(P)"));
    }
    public function tickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function referrals()
    {
        return $this->belongsToMany(Account::class, "client_referrals");
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
}
