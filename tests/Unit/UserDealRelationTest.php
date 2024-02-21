<?php

namespace Tests\Unit;

use App\Broker;
use App\Deal;
use App\Termsheet;
use App\User;
use Database\Seeders\Termsheets;
use Tests\TestCase;

class UserDealRelationTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testStoreRelation()
    {
        $user = User::factory()->create();
        $broker = Broker::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $deal = Deal::factory()->create(['user_id' => $broker->id]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $this->assertDatabaseHas('user_deal', [
            'user_id' => $user->id,
            'deal_id' => $deal->id,
            'relation_type' => 3,
        ]);
    }

    public function testCheckRelation()
    {
        $user = User::factory()->create();
        $broker = Broker::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $deal = Deal::factory()->create(['user_id' => $broker->id]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $checkRelatedDeal = $user->checkRelatedDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $this->assertTrue(count($checkRelatedDeal) == 1);
    }

    public function testRemoveRelationSave()
    {
        $user = User::factory()->create();
        $broker = Broker::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $deal = Deal::factory()->create(['user_id' => $broker->id]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_SAVE_DEAL);

        $user->removeRelation($deal->id, User::LENDER_SAVE_DEAL);

        $checkRelatedDeal = $user->checkRelatedDeal($deal->id, User::LENDER_SAVE_DEAL);

        $this->assertTrue(count($checkRelatedDeal) == 0);
    }

    public function testRemoveRelationArchive()
    {
        $user = User::factory()->create();
        $broker = Broker::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $deal = Deal::factory()->create(['user_id' => $broker->id]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_ARCHIVE_DEAL);

        $user->removeRelation($deal->id, User::LENDER_ARCHIVE_DEAL);

        $checkRelatedDeal = $user->checkRelatedDeal($deal->id, User::LENDER_ARCHIVE_DEAL);

        $this->assertTrue(count($checkRelatedDeal) == 0);
    }
}
