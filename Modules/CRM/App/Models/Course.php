<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CurrencyRate;

class Course extends Model
{
    protected $table = 'courses';

    protected $guarded = [];

    // Relation to university
    public function university()
    {
        return $this->belongsTo(University::class, 'university_id', 'id');
    }

    public function getOriginalFeeAttribute()
    {
        return $this->currency_symbol . number_format($this->tuition_fee);
    }

    // public function getConvertedFee($targetCurrency = 'INR')
    // {
    //     // If already same currency
    //     if ($this->currency_code === $targetCurrency) {
    //         return [
    //             'amount' => $this->tuition_fee,
    //             'currency' => $targetCurrency
    //         ];
    //     }

    //     // Step 1: find rate FROM course currency → INR
    //     $rate = CurrencyRate::where('base_currency', 'USD') // your table base
    //         ->where('target_currency', $this->currency_code)
    //         ->value('rate');

    //     if (!$rate) {
    //         return null; // fail-safe
    //     }

    //     // Step 2: convert to USD first
    //     $amountInUSD = $this->tuition_fee / $rate;

    //     // Step 3: USD → target currency
    //     $targetRate = CurrencyRate::where('base_currency', 'USD')
    //         ->where('target_currency', $targetCurrency)
    //         ->value('rate');

    //     if (!$targetRate) {
    //         return null;
    //     }

    //     return [
    //         'amount' => round($amountInUSD * $targetRate),
    //         'currency' => $targetCurrency
    //     ];
    // }

    //  public function getConvertedFee(string $targetCurrency = 'INR')
    // {
    //     $amount = $this->tuition_fee;
    //     $sourceCurrency = $this->currency_code;

    //     // Same currency → no conversion
    //     if ($sourceCurrency === $targetCurrency) {
    //         return [
    //             'amount'   => $amount,
    //             'currency' => $targetCurrency,
    //             'symbol'   => $this->symbolFor($targetCurrency),
    //         ];
    //     }

    //     /*
    //      | STEP 1: source → USD
    //      */
    //     if ($sourceCurrency !== 'USD') {
    //         $usdToSource = CurrencyRate::where('base_currency', 'USD')
    //             ->where('target_currency', $sourceCurrency)
    //             ->value('rate');

    //         if (!$usdToSource) return null;

    //         $amount = $amount / $usdToSource;
    //     }

    //     /*
    //      | STEP 2: USD → target
    //      */
    //     if ($targetCurrency !== 'USD') {
    //         $usdToTarget = CurrencyRate::where('base_currency', 'USD')
    //             ->where('target_currency', $targetCurrency)
    //             ->value('rate');

    //         if (!$usdToTarget) return null;

    //         $amount = $amount * $usdToTarget;
    //     }

    //     return [
    //         'amount'   => round($amount),
    //         'currency' => $targetCurrency,
    //         'symbol'   => $this->symbolFor($targetCurrency),
    //     ];
    // }
    public function getConvertedFee(
        int|float|string $amount,
        string $targetCurrency = 'INR'
    ) {
        $amount = (float) $amount; // 🔥 FIX
        $sourceCurrency = $this->currency_code;

        if ($sourceCurrency === $targetCurrency) {
            return [
                'amount'   => $amount,
                'currency' => $targetCurrency,
                'formatted'  => $this->formatCurrency($amount, $targetCurrency),
                'symbol'   => $this->symbolFor($targetCurrency),
            ];
        }

        // STEP 1: source → USD
        if ($sourceCurrency !== 'USD') {
            $usdToSource = CurrencyRate::where('base_currency', 'USD')
                ->where('target_currency', $sourceCurrency)
                ->value('rate');

            if (!$usdToSource) return null;

            $amount = $amount / $usdToSource;
        }

        // STEP 2: USD → target
        if ($targetCurrency !== 'USD') {
            $usdToTarget = CurrencyRate::where('base_currency', 'USD')
                ->where('target_currency', $targetCurrency)
                ->value('rate');

            if (!$usdToTarget) return null;

            $amount = $amount * $usdToTarget;
        }

        return [
            'amount'   => round($amount),
            'currency' => $targetCurrency,
            'formatted'  => $this->formatCurrency($amount, $targetCurrency),
            'symbol'   => $this->symbolFor($targetCurrency),
        ];
    }

    
    private function symbolFor(string $currency): string
    {
        return match ($currency) {
            'INR' => '₹',
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            'AUD' => 'A$',
            'CAD' => 'C$',
            default => $currency . ' ',
        };
    }

    public function formatCurrency(float $amount, string $currency): string
    {
        $amount = round($amount, 2);
        $symbol = $this->symbolFor($currency);

        if (class_exists(\NumberFormatter::class)) {

            $locale = match ($currency) {
                'INR' => 'en_IN',
                'USD' => 'en_US',
                'GBP' => 'en_GB',
                'EUR' => 'de_DE',
                'AUD' => 'en_AU',
                'CAD' => 'en_CA',
                default => 'en_US',
            };

            $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency($amount, $currency);

            return $formatted . ' ' . $currency;
        }

        // Fallback if intl not installed
        if ($currency === 'INR') {
            return $symbol . $this->formatIndianNumber($amount) . ' ' . $currency;
        }

        return $symbol . number_format($amount, 2) . ' ' . $currency;
    }


    private function formatIndianNumber($num)
    {
        $num = round($num, 2);
        $parts = explode('.', number_format($num, 2, '.', ''));
        $int = $parts[0];
        $dec = '.' . $parts[1];

        $lastThree = substr($int, -3);
        $restUnits = substr($int, 0, -3);

        if ($restUnits != '') {
            $lastThree = ',' . $lastThree;
        }

        $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);

        return $restUnits . $lastThree . $dec;
    }

}
