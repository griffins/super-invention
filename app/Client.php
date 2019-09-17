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
        $copyTime = AcruedAmount::query()->where('created_at', '<=', $emailExtract->time)->orderByDesc('created_at')->limit(1)->first();
        if ($copyTime) {
            $copyTime = $copyTime->created_at->addMinutes(45);
        } else {
            $copyTime = Transaction::query()
                ->where('created_at', '<', $emailExtract->time->copy())
                ->orderByDesc('created_at')
                ->first()
                ->created_at;
        }

        $totalBalance = Transaction::query()->where('created_at', '<=', $copyTime)->balance();
        $profits = $emailExtract->balance - $totalBalance;
        $master = Client::query()->find(1);
        DB::beginTransaction();
        $moneyLeft = $profits;
        AcruedAmount::query()->create(['amount' => $emailExtract->balance, 'created_at' => $emailExtract->time, 'message_id' => $emailExtract->mailId, 'item' => 'BTC']);
        Client::query()->whereNotIn('id', [1])->chunk(20, function ($clients) use ($profits, $totalBalance, $emailExtract, $copyTime, $master, &$moneyLeft) {
            foreach ($clients as $client) {
                $clientBalance = $client->transactions()->where('created_at', '<=', $copyTime)->balance();
                $transaction = new TransactionExtract();
                $transaction->ticket = $emailExtract->mailId;
                $transaction->item = $emailExtract->item;
                $transaction->type = 'profit';
                $transaction->time = $emailExtract->time;
                if ($totalBalance != 0) {
                    $transaction->amount = ($clientBalance / $totalBalance) * $profits * $client->profits / 100;
                    $moneyLeft -= $transaction->amount;
                    Transaction::fromExtract($transaction, $client);
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
