<?php


namespace App\Foundation\Statement;


use App\Account;
use App\Client;
use App\Transaction;
use Carbon\Carbon;

class EmailExtract
{
    public $balance = 0;
    public $item;
    public $mailId = 0;
    public $time = 0;

    public static function process(Account $account, $email)
    {
        $extract = new static();
        if (preg_match("/[0-9|.]+\s+BTC/", $email->body, $matches) === 1) {
            $extract->balance = str_replace(' BTC', '', $matches[0]);
            $extract->mailId = $email->message_id;
            $extract->item = "BTC";
            $extract->time = Carbon::createFromTimestampUTC($email->udate);
            if (in_array($email->from, ['jackryland@coin-consultant.net', 'noreply@mail.l7.trade']) && !Transaction::query()->where('ticket', $extract->mailId)->exists()) {
                Client::updateBalances($account, $extract);
            }
        }
    }
}
