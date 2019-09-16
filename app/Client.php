<?php

namespace App;

use App\Foundation\Statement\EmailExtract;
use App\Foundation\Statement\TransactionExtract;
use App\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

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
        'name', 'email', 'password', 'notes', 'profits', 'wallet', 'status'
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


    public function getAccountBalanceAttribute()
    {
        return $this->transactions()->balance();
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public static function updateBalances(EmailExtract $emailExtract)
    {
        $balanceBefore = Transaction::query()->where('created_at', '<=', $emailExtract->time->startOfDay())->balance();
        $profits = $emailExtract->balance - $balanceBefore;
        $master = Client::query()->find(1);
        DB::beginTransaction();
        $moneyLeft = $profits;
        AcruedAmount::query()->create(['amount' => $emailExtract->balance, 'created_at' => $emailExtract->time->toDateTimeString(), 'message_id' => $emailExtract->mailId, 'item' => 'BTC']);
        Client::query()->chunk(20, function ($clients) use ($profits, $balanceBefore, $emailExtract, $master, $moneyLeft) {
            foreach ($clients as $client) {
                $balance = $client->transactions()->where('created_at', '<=', $emailExtract->time->startOfDay())->balance();
                $transaction = new TransactionExtract();
                $transaction->ticket = $emailExtract->mailId;
                $transaction->item = $emailExtract->item;
                $transaction->type = 'profit';
                $transaction->time = $emailExtract->time;
                if ($balanceBefore != 0) {
                    $transaction->amount = ($balance / $balanceBefore) * $profits * $client->profits / 100;
                    $moneyLeft -= $transaction->amount;
                    Transaction::fromExtract($transaction, $client);

                    $transaction->amount = ($balance / $balanceBefore) * $profits * (100 - $client->profits) / 100;
                    $moneyLeft -= $transaction->amount;
                    if ($client->profits != 100) {
                        Transaction::fromExtract($transaction, $master);
                    }
                }
            }
        });
        $transaction = new TransactionExtract();
        $transaction->ticket = $emailExtract->mailId;
        $transaction->item = $emailExtract->item;
        $transaction->type = 'profit';
        $transaction->amount = $moneyLeft;
        $transaction->time = $emailExtract->time;
        Transaction::fromExtract($transaction, $master);
        DB::commit();
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
