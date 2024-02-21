<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\QuoteMapper;
use App\Quote;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class QuoteMigrationCustomPercentage
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class QuoteMigrationCustomPercentage extends Controller
{
    /**
     * @var Collection
     */
    private $quotes;

    /**
     * Change structure for other expenses field
     */
    public function updateCustomPercentage()
    {
        $this
            ->loadAllQuotes()
            ->updateData();
    }

    /**
     * Get all quotes
     *
     * @return $this
     */
    private function loadAllQuotes()
    {
        $this->quotes = Quote::all();

        return $this;
    }

    /**
     * @return $this
     */
    private function updateData()
    {
        $this->quotes
            ->each(function (Quote $quote) {
                // Prepare data
                $mapper = $this->getMapper($quote);
                $mappedQuote = $this->getMappedQuote($mapper);
                $user = $this->getUser($quote);

                // Change field custom percentage
                if (isset($mappedQuote['purchaseAndRefinanceLoans']['prePaymentCustomYear'])) {
                    if (isset($mappedQuote['lastStepStatus'])) {
                        unset($mappedQuote['lastStepStatus']);
                    }
                    $mappedQuote['purchaseAndRefinanceLoans']['prePaymentCustomYear'] = $this->updateValue($mappedQuote['purchaseAndRefinanceLoans']['prePaymentCustomYear']);
                }

                // Persist data
                $this->persistData($mapper, $mappedQuote, $user);
            });

        return $this;
    }

    private function getMapper(Quote $quote)
    {
        try {
            return new QuoteMapper($quote->id);
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            throw $e;
        }
    }

    private function getMappedQuote($mapper)
    {
        return $mapper->mapFromEloquent();
    }

    private function getUser(Quote $quote)
    {
        return User::find($quote->user_id);
    }

    /**
     * Change field
     *
     * @param $prePaymentCustomYear
     * @return array[]
     */
    private function updateValue($prePaymentCustomYear): array
    {
        if (empty($prePaymentCustomYear)) {
            return [];
        }

        return array_map(function ($value) {
            if (gettype($value) === 'integer') {
                return strval($value);
            }

            return $value;
        }, $prePaymentCustomYear);
    }

    /**
     * Save changed structure of field
     *
     * @param  QuoteMapper  $mapper
     * @param  array  $mappedQuote
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @return $this
     */
    private function persistData(QuoteMapper $mapper, array $mappedQuote, \Illuminate\Foundation\Auth\User $user)
    {
        $quote = $mapper->mapToEloquent($mappedQuote, $user);
        $quote->save();

        return $this;
    }
}
