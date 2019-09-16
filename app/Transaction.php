<?php

namespace App;

use App\Foundation\Statement\TransactionExtract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = ['id', 'dom'];
    protected $dates = ['opened_at', 'closed_at'];

    public static function fromExtract(TransactionExtract $extract, Client $client)
    {
        if ($extract->amount != 0) {
            $transaction = static::query()
                ->firstOrNew(['ticket' => $extract->ticket, 'created_at' => $extract->time->toDateTimeString(), 'amount' => $extract->amount, 'type' => $extract->type, 'item' => $extract->item, 'client_id' => $client->id]);
            $transaction->save();
        }
    }

    public function scopeProfit(Builder $query)
    {
        return ($query
            ->whereIn('type', ['profit'])
            ->selectRaw("sum(amount) as aggregate")->value('aggregate')) ?: 0;
    }

    public function scopeBalance(Builder $query)
    {
        return ($query
            ->whereIn('type', ['withdraw', 'deposit', 'profit'])
            ->selectRaw("sum( CASE WHEN type = 'withdraw' THEN 0 - amount ELSE amount END ) as aggregate")->value('aggregate')) ?: 0;
    }

    public function scopeDeposits(Builder $query)
    {
        return $query
            ->where('type', 'deposit');
    }

    public function scopeByAdminRole(Builder $query)
    {
        return user()->club == '*' ? $query : $query->whereIn('account_id', Account::query()->whereIn('server_id', Server::query()->where('name', 'regexp', sprintf("^(P)"))->pluck('id'))->pluck('id'));
    }

    public function scopeWithdrawals(Builder $query)
    {
        return $query
            ->where('type', 'withdraw');
    }
}
