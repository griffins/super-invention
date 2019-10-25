<?php

namespace App\Console\Commands;

use App\Account;
use App\AcruedAmount;
use App\Foundation\Statement\EmailExtract;
use App\Mail\MailReader;
use App\Transaction;
use Illuminate\Console\Command;

class ImportStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:statements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap data from email statements';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accounts = Account::query()->get();
        $mails = collect();

        foreach ($accounts as $account) {
            $mail = new MailReader($account->email, $account->password);
            $mails = $mails->merge($mail->emailsLastFourDays()->map(function ($a) use ($account) {
                $a->account = &$account;
                return $a;
            }));
        }

        $mails = $mails->sort(function ($a, $b) {
            return $a->udate > $b->udate;
        });

        foreach ($mails as $email) {
            try {
                EmailExtract::process($email->account, $email);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        $this->comment("Peak Memory:" . memory_get_peak_usage());
    }
}
