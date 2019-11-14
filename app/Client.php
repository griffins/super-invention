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
        'name', 'email', 'password', 'notes', 'profits', 'commission', 'status', 'account_id', 'client_deposit_total'
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

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public static function updateBalances(Account $account, EmailExtract $emailExtract)
    {
        $copyTime = AcruedAmount::query()
            ->where('created_at', '<=', $emailExtract->time)
            ->where('account_id', $account->id)
            ->orderByDesc('created_at')->limit(1)->first();

        if ($copyTime) {
            $copyTime = $copyTime->created_at->addMinutes(45);
        } else {
            $copyTime = Transaction::query()
                ->where('created_at', '<', $emailExtract->time->copy())
                ->where('account_id', $account->id)
                ->orderByDesc('created_at')
                ->first()
                ->created_at;
        }

        $totalBalance = Transaction::query()
            ->where('account_id', $account->id)
            ->where('created_at', '<=', $copyTime)
            ->balance();
        $count = Transaction::query()
            ->where('account_id', $account->id)
            ->where('created_at', '<=', $copyTime)->count();
        $totalClubBalance = Transaction::query()
            ->where('created_at', '<=', $copyTime)
            ->balance();

        $profits = $emailExtract->balance - $totalBalance;
        $master = Client::query()->find(1);
        DB::beginTransaction();
        $moneyLeft = $profits;
        dump($copyTime->format('jS H:i') . ': [' . $totalBalance . '(' . $count . ')' . ' -> ' . currency($emailExtract->balance, true, 8) . '] P/L:' . currency($profits, true, 8));
        AcruedAmount::query()->create(['amount' => $emailExtract->balance, 'account_id' => $account->id, 'created_at' => $emailExtract->time, 'message_id' => $emailExtract->mailId, 'item' => 'BTC']);
        Client::query()->whereNotIn('id', [1])->chunk(20, function ($clients) use ($profits, $totalBalance, $totalClubBalance, $account, $emailExtract, $copyTime, $master, &$moneyLeft) {
            foreach ($clients as $client) {
                $clientBalance = $client->transactions()->where('created_at', '<=', $copyTime)->balance();
                $transaction = new TransactionExtract();
                $transaction->ticket = $emailExtract->mailId;
                $transaction->item = $emailExtract->item;
                $transaction->type = 'profit';
                $transaction->account_id = $account->id;
                $transaction->time = $emailExtract->time;
                if ($totalBalance != 0) {
                    $transaction->amount = ($clientBalance / $totalClubBalance) * $profits * $client->profits / 100;
                    $moneyLeft -= $transaction->amount;
                    Transaction::fromExtract($transaction, $client);
                }
            }
        });
        $transaction = new TransactionExtract();
        $transaction->ticket = $emailExtract->mailId;
        $transaction->item = $emailExtract->item;
        $transaction->type = 'profit';
        $transaction->account_id = $account->id;
        $transaction->amount = $moneyLeft;
        $transaction->time = $emailExtract->time;
        Transaction::fromExtract($transaction, $master);
        DB::commit();
    }

    public static function updateBalances2(Account $account, $profit_type, $profit_value, $date)
    {
        $master = Client::query()->find(1);
        DB::beginTransaction();
        $totalClubBalance = Transaction::query()->where('created_at', '<=', $date)
            ->balance();
        if ($profit_type == 'btc') {
            $profits = $profit_value;
        } else {
            $profits = $profit_value * $totalClubBalance / 100;
        }
        $moneyLeft = $profits;
        AcruedAmount::query()->create(['amount' => $profits, 'account_id' => $account->id, 'created_at' => $date, 'message_id' => md5($date), 'item' => 'BTC']);
        Client::query()->whereNotIn('id', [1])->chunk(20, function ($clients) use ($totalClubBalance, $profits, $date, $account, $master, &$moneyLeft) {
            foreach ($clients as $client) {
                $clientBalance = $client->transactions()->where('created_at', '<=', $date)->balance();

                $transaction = new TransactionExtract();
                $transaction->ticket = md5($date);
                $transaction->item = 'BTC';
                $transaction->type = 'profit';
                $transaction->account_id = $account->id;
                $transaction->time = $date;

                $transaction->amount = ($clientBalance / $totalClubBalance) * $profits * $client->profits / 100;
                $moneyLeft -= $transaction->amount;
                Transaction::fromExtract($transaction, $client);

            }
        });
        $transaction = new TransactionExtract();
        $transaction->ticket = md5($date);
        $transaction->item = 'BTC';
        $transaction->type = 'profit';
        $transaction->account_id = $account->id;
        $transaction->amount = $moneyLeft;
        $transaction->time = $date;
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
