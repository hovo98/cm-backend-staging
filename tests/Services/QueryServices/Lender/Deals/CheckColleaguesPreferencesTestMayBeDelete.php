<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Lender;
use App\Services\QueryServices\Lender\Brokers\BrokersConnectedToMultipleLenders;
use App\Services\QueryServices\Lender\SameDomainLenders;
use Tests\TestCase;

// Todo: Maybe we can delete this one because the business logic tested is not true anymore
/**
 * Class CheckColleaguesPreferencesTest
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CheckColleaguesPreferencesTestMayBeDelete //extends TestCase
{
    //    /**
    //     * @var SameDomainLenders
    //     */
    //    private $sameDomainLendersService;
    //
    //    /**
    //     * @var BrokersConnectedToMultipleLenders
    //     */
    //    private $brokersConnectedToMultipleLenders;
    //
    //    /** @var Lender */
    //    private $mainLender;
    //
    //    /** @var Lender */
    //    private $lender1;
    //
    //    /** @var Lender */
    //    private $lender2;
    //
    //    /** @var Lender */
    //    private $lender3;
    //
    //    protected function setUp(): void
    //    {
    //        parent::setUp();
    //
    //        $this->sameDomainLendersService = $this->app->make(SameDomainLenders::class);
    //
    //        $this->mainLender = factory(Lender::class)->create(['email' => 'email@domain.com',
    //            'metas' => [
    //                'perfect_fit' => [
    //                    'areas' => [
    //                        [
    //                            'area' => [
    //                                'place_id' => 'ChIJOwg_06VPwokRYv534QaPC8g',
    //                                'long_name' => 'New York',
    //                                'formatted_address' => 'New York, NY, USA'
    //                            ],
    //                            'exclusions' => [
    //                                [
    //                                    'place_id' => 'ChIJsXxpOlWLwokRd1zxj6dDblU',
    //                                    'long_name' => 'The Bronx',
    //                                    'formatted_address' => 'The Bronx, NY, USA'
    //                                ],
    //                                [
    //                                    'place_id' => 'ChIJCSF8lBZEwokRhngABHRcdoI',
    //                                    'long_name' => 'Brooklyn',
    //                                    'formatted_address' => 'Brooklyn, NY, USA'
    //                                ]
    //                            ]
    //                        ],
    //                        [
    //                            'area' => [
    //                                'place_id' => 'ChIJvypWkWV2wYgR0E7HW9MTLvc',
    //                                'long_name' => 'Florida',
    //                                'formatted_address' => 'Florida, USA'
    //                            ],
    //                            'exclusions' => []
    //                        ],
    //                        [
    //                            'area' => [
    //                                'place_id' => 'ChIJSTKCCzZwQIYRPN4IGI8c6xY',
    //                                'long_name' => 'Texas',
    //                                'formatted_address' => 'Texas, USA'
    //                            ],
    //                            'exclusions' => [
    //                                [
    //                                    'place_id' => 'ChIJAYWNSLS4QIYROwVl894CDco',
    //                                    'long_name' => 'Houston',
    //                                    'formatted_address' => 'Houston, TX, USA'
    //                                ],
    //                            ]
    //                        ]
    //                    ],
    //                    'loan_size' => [
    //                        'max' => 15000000,
    //                        'min' => 5000000
    //                    ],
    //                    'asset_types' => [5],
    //                    'multifamily' => null
    //                ]
    //            ]
    //        ]);
    //
    //        $this->lender1 = factory(Lender::class)->create(['email' => 'email1@domain.com',
    //            'metas' => [
    //                'perfect_fit' => [
    //                    'areas' => [
    //                        [
    //                            'area' => [
    //                                'place_id' => 'ChIJOwg_06VPwokRYv534QaPC8g',
    //                                'long_name' => 'New York',
    //                                'formatted_address' => 'New York, NY, USA'
    //                            ],
    //                            'exclusions' => [
    //                                [
    //                                    'place_id' => 'ChIJsXxpOlWLwokRd1zxj6dDblU',
    //                                    'long_name' => 'The Bronx',
    //                                    'formatted_address' => 'The Bronx, NY, USA'
    //                                ],
    //                                [
    //                                    'place_id' => 'ChIJCSF8lBZEwokRhngABHRcdoI',
    //                                    'long_name' => 'Brooklyn',
    //                                    'formatted_address' => 'Brooklyn, NY, USA'
    //                                ]
    //                            ]
    //                        ],
    //                        [
    //                            'area' => [
    //                                'place_id' => 'ChIJvypWkWV2wYgR0E7HW9MTLvc',
    //                                'long_name' => 'Florida',
    //                                'formatted_address' => 'Florida, USA'
    //                            ],
    //                            'exclusions' => []
    //                        ],
    //                        [
    //                            'area' => [
    //                                'place_id' => 'ChIJSTKCCzZwQIYRPN4IGI8c6xY',
    //                                'long_name' => 'Texas',
    //                                'formatted_address' => 'Texas, USA'
    //                            ],
    //                            'exclusions' => [
    //                                [
    //                                    'place_id' => 'ChIJAYWNSLS4QIYROwVl894CDco',
    //                                    'long_name' => 'Houston',
    //                                    'formatted_address' => 'Houston, TX, USA'
    //                                ],
    //                            ]
    //                        ]
    //                    ],
    //                    'loan_size' => [
    //                        'max' => 15000000,
    //                        'min' => 5000000
    //                    ],
    //                    'asset_types' => [4,2],
    //                    'multifamily' => null
    //                ]
    //            ]
    //        ]);
    //
    //        $this->lender1 = factory(Lender::class)->create(['email' => 'email1@domain.com']);
    //        $this->lender2 = factory(Lender::class)->create(['email' => 'email2@domain.com']);
    //        $this->lender3 = factory(Lender::class)->create(['email' => 'newemail@testing.com']);
    //    }
    //
    //    public function testRun()
    //    {
    //        $result = $this->service->run(['id' => $this->mainLender->id, 'domain' => $this->mainLender->domain]);
    //
    //        $this->assertContains($this->lender1->id, $result);
    //        $this->assertContains($this->lender2->id, $result);
    //        $this->assertNotContains($this->lender3->id, $result);
    //    }

    //$colleagues = $this->sameDomainLenders->query($id, $domain);
    //$brokersConnectedToColleagues = $this->brokersConnectedToMultipleLenders->query($colleagues);
    //$checkDeals = $this->checkColleaguesPreferences->query($colleagues->get()->pluck('id')->toArray(), $brokersConnectedToColleagues);
}
