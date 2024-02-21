<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $mapper = new \App\DataTransferObjects\QuoteMapper();
        $data = [
            'constructionLoans' => [
                'requestedLoan' => [
                    'dollarAmount' => 121221,
                    'loanValue' => 111,
                    'loanCost' => 111,
                ],
                'landCosts' => [
                    'costAmount' => 111,
                    'costPercent' => 11,
                ],
                'softCosts' => [
                    'costAmount' => 11111,
                    'costPercent' => 11,
                ],
                'hardCosts' => [
                    'costAmount' => 11,
                    'costPercent' => 11,
                ],
                'lendTowardsCosts' => [
                    'costAmount' => 11,
                    'costPercent' => 11,
                ],
                'interestRateType' => 1,
                'interestRate' => [
                    'fixedRateAmount' => 11,
                    'yieldText' => '3',
                    'spread' => 11,
                    'floor_rate' => 11,
                ],
                'constructionTerm' => '222',
                'extensionOptionType' => 1,
                'extensionOption' => [
                    'duration' => 'aaa',
                    'feeAmount' => 11,
                    'feePercentage' => 11,
                    'allowed' => 1,
                ],
                'recourseOptionType' => 1,
                'recourseType' => 1,
                'collectingFeeType' => 1,
                'collectingFee' => [
                    'feePercent' => 1,
                    'feeAmount' => 1,
                ],
                'exitFeeType' => 1,
                'exitFee' => [
                    'fee' => [
                        'feePercent' => 1,
                        'feeAmount' => 1,
                    ],
                    'comment' => 'aaa',
                ],
                'permanentLoanOptionType' => 1,
            ],
            'purchaseAndRefinanceLoans' => $mapper->QuotePurchaseAndRefinanceLoansUnit(),
            'message' => 'I\m excited to deliver this great offer to you!',
        ];

        return [
            'finished' => true,
            'deal_id' => 32507,
            'user_id' => 10,
            'data' => $data,
            'finished_at' => '2021-05-12 11:22:33',
        ];
    }

    public function finished()
    {
        return $this->state(function (array $attributes) {
            return [
                'finished' => true,
                'finished_at' => now(),
            ];
        });
    }
}
