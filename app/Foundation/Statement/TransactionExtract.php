<?php


namespace App\Foundation\Statement;


class TransactionExtract
{
    /**
     * @var $dom EmailExtract
     */
    public $ticket;
    public $account_id;
    public $type;
    public $item;
    public $amount;
    public $time;
}