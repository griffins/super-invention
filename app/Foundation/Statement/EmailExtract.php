<?php


namespace App\Foundation\Statement;


use App\Account;
use App\Client;

class EmailExtract
{
    public $balance = 0;
    public $item;
    public $mailId = 0;

    public static function process($email)
    {
        $extract = new static();
        if (preg_match("/[0-9|.]+\s+BTC/", $email->body, $matches) === 1) {
            $extract->balance = str_replace(' BTC', '', $matches[0]);
            $extract->mailId = $email->message_id;
            $extract->item = "BTC";
            if (in_array($email->from, ['jackryland@coin-consultant.net'])) {
                Client::updateBalances($extract);
            }
        }
    }
}