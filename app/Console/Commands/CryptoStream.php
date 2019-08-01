<?php

namespace App\Console\Commands;

use App\CryptoTrade;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class CryptoStream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:stream';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Capture crypto stream from arbistar';

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
        $client = new Client();
        try {
            $stream = json_decode($client->get('http://community.arbistar.com:3000/community-bot')->getBody()->getContents(), true);
            if ($stream) {
                foreach ($stream as $trade) {
                    $trade['trade_id'] = Arr::pull($trade, 'id');
                    $trade['date'] = strpos($trade['date'], ':') === false ?
                        Carbon::createFromTimestampMs($trade['date'])
                        : Carbon::parse($trade['date']);
                    CryptoTrade::query()->firstOrCreate($trade);
                }
            }
        } catch (\Exception $e) {

        }
    }
}
