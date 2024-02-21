<?php

namespace Tests\Unit\GraphQL\Queries\Payments;

use App\User;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class UserPlanTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function it_can_get_a_users_active_plan(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);

        Subscription::factory()->create([
            'user_id' => $broker->id,
            'stripe_price' => env('STRIPE_LARGE_PRICE_ID')
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                query {
                    plan {
                        name
                        status
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'plan' => [
                        'name',
                        'status'
                    ]
                ]
            ]);
    }
}
