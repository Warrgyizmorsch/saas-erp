<?php

namespace Modules\CRM\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Modules\CRM\App\Models\CurrencyRate;

class FetchCurrencyRates extends Command
{
    protected $signature = 'currency:fetch';
    protected $description = 'Fetch and store currency rates daily';

    public function handle()
    {
        $apiKey = env('EXCHANGE_RATE_API_KEY');
        $baseCurrency = 'USD';
        $today = now()->toDateString();

        $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/latest/{$baseCurrency}";
        $response = Http::get($url);

        if (!$response->successful()) {
            $this->error('Currency API request failed');
            return Command::FAILURE;
        }

        $data = $response->json();

        if (!isset($data['conversion_rates'])) {
            $this->error('Invalid API response');
            return Command::FAILURE;
        }

        foreach ($data['conversion_rates'] as $currency => $rate) {
            DB::table('currency_rates')->updateOrInsert(
                [
                    'base_currency'   => $baseCurrency,
                    'target_currency' => $currency,
                ],
                [
                    'rate'       => $rate,
                    'fetched_at' => $today,
                    'updated_at' => now(),
                ]
            );
        }

        $this->info('Currency rates updated successfully');
        return Command::SUCCESS;
    }

}

