<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Broker\Deals;

use App\Deal;
use App\Jobs\Broker\Deal\ShareDeal as JobShareDeal;
use App\Services\QueryServices\AbstractQueryService;
use App\User;

/**
 * Class ShareDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ShareDeal extends AbstractQueryService
{
    public function run($args)
    {
        $deals = Deal::find($args['deals']);
        $errorMessage = [];
        foreach ($deals as $deal) {
            foreach ($args['emails'] as $email) {
                if (! $deal->finished) {
                    $errorMessage[] = [
                        'message' => 'The deal is not published.',
                    ];
                }
                $emailChecked = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

                // If there is no email continue
                if (! $emailChecked) {
                    $errorMessage[] = [
                        'message' => 'Please enter a valid email address',
                    ];
                }

                $lender = User::where('email', $email)->first();

                if (! $lender) {
                    $errorMessage[] = [
                        'message' => 'User '.$email.' is unable to receive emails right now.',
                    ];

                    continue;
                }

                $broker = User::find($args['broker']);
                $senderName = $broker->name();
                $brokerCompany = $broker->getCompany()['company_name'];
                $dollarAmount = $this->formatDollarAmountDeal($deal->dollar_amount);
                $loanType = ucfirst(strtolower(Deal::LOAN_TYPE[$deal->data['loan_type']]));

                if ($lender && $lender->role === 'lender') {
                    $url = config('app.frontend_url').'/individual-deal/'.$deal->id;
                    $existingAccount = true;
                    JobShareDeal::dispatch($email, $senderName, $dollarAmount, $loanType, $url, $existingAccount, $lender->first_name, '');
                } elseif (! $lender && $lender !== 'broker') {
                    $url = config('app.frontend_url').'/sign-up/';
                    $existingAccount = false;
                    JobShareDeal::dispatch($email, $senderName, $dollarAmount, $loanType, $url, $existingAccount, '', $brokerCompany);
                } else {
                    $errorMessage[] = [
                        'message' => 'Looks like this is a broker\'s email address. Please enter a lender\'s email address.',
                    ];
                }
            }
        }
        $errorMessage = array_unique($errorMessage, SORT_REGULAR);

        return [
            'errorMessage' => $errorMessage,
        ];
    }

    /**
     * @param $amount
     * @return string
     */
    private function formatDollarAmountDeal($amount): string
    {
        if ($amount !== 0 || $amount !== '0') {
            $fmt = new \NumberFormatter('en-US', \NumberFormatter::CURRENCY);
            $amountDollar = $fmt->formatCurrency((int) $amount, 'USD');
            $formatAmount = str_replace('.00', '', $amountDollar);
            if ($formatAmount === '$0') {
                return '';
            }

            return $formatAmount;
        }

        return '';
    }
}
