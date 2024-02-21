<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Broker\Quotes;

use App\DataTransferObjects\QuoteMapper;
use App\Interfaces\IndividualMapperService;
use App\Lender;
use App\Quote;
use App\User;

/**
 * Class Individual
 */
class Individual implements IndividualMapperService
{
    public function map($obj)
    {
        return [
            'lender' => $this->mappedLender($obj),
            'quotes' => $this->mappedQuotes($obj->quotes),
        ];
    }

    protected function mappedLender(Lender $lender)
    {
        return [
            'id' => $lender->id,
            'company' => $this->getLenderCompanyName($lender->id),
            'profile_img' => $this->getLenderProfileImage($lender->id),
        ];
    }

    /**
     * @param  Quote[]  $quotes
     * @return array
     *
     * @throws \Exception
     */
    protected function mappedQuotes($quotes)
    {
        $mappedQuotes = [];
        foreach ($quotes as $quote) {
            $button = $quote->getQuoteStatusButton();
            $quoteMapper = new QuoteMapper($quote->id);
            $mappedQuote = $quoteMapper->mapFromEloquent();
            $mappedQuote['button'] = $button;
            $mappedQuote['olderThanTwoWeeks'] = $quote->isOlderThanTwoWeeks();
            $mappedQuote['secondAccept'] = $quote->isSecondAccept();
            $mappedQuotes[] = $mappedQuote;
        }

        return $mappedQuotes;
    }

    private function getLenderCompanyName($lenderId)
    {
        return User::find($lenderId)?->getCompanyNameFromMetasOrFromCompanyRelationship();
    }

    private function getLenderProfileImage($lenderId)
    {
        $userLender = User::find($lenderId);

        return $userLender->profile_image;
    }
}
