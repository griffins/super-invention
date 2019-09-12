<?php

namespace App\Console\Commands;

use App\Foundation\Statement\EmailExtract;
use App\Mail\MailReader;
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
        $mail = new MailReader(env('MAILBOX_USERNAME'),env('MAILBOX_PASSWORD'));
        foreach ($mail->emailsLastTwoDays() as $email) {
            try {
                EmailExtract::process($email);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        $this->comment("Peak Memory:" . memory_get_peak_usage());
    }
}
