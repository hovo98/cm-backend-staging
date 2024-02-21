<?php

namespace Tests\Unit\Models;

use App\Broker;
use App\Company;
use App\Lender;
use App\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function getCompanyNameFromMetasOrFromCompanyRelationship_can_work_without_any_company_data_at_all()
    {
        $user = User::factory()->create([
            'metas' => [],
            'company_id' => null,
        ]);

        $this->assertNull($user->getCompanyNameFromMetasOrFromCompanyRelationship());
    }

    /** @test */
    public function getCompanyNameFromMetasOrFromCompanyRelationship_knows_about_company_name_using_only_metas()
    {
        $user = User::factory()->create([
            'metas' =>[
                'company_data' => [
                    'company_name' => 'Company name from metas',
                ]
            ]
        ]);

        $this->assertEquals('Company name from metas', $user->getCompanyNameFromMetasOrFromCompanyRelationship());
    }

    /** @test */
    public function getCompanyNameFromMetasOrFromCompanyRelationship_knows_about_company_name_using_only_metas_even_when_the_user_company_relationship_is_present()
    {
        $company = Company::factory()->create([
            'company_name' => 'A random company name',
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'metas' =>[
                'company_data' => [
                    'company_name' => 'Company name from metas',
                ]
            ]
        ]);

        $this->assertEquals('Company name from metas', $user->getCompanyNameFromMetasOrFromCompanyRelationship());
    }

    /** @test */
    public function getCompanyNameFromMetasOrFromCompanyRelationship_knows_about_company_name_using_company_relationship()
    {
        $company = Company::factory()->create([
            'company_name' => 'A random company name',
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $this->assertEquals('A random company name', $user->getCompanyNameFromMetasOrFromCompanyRelationship());
    }

    /** @test */
    public function broker_getCompanyNameFromMetasOrFromCompanyRelationship_knows_about_company_name_using_company_relationship()
    {
        $company = Company::factory()->create([
            'company_name' => 'A random company name',
        ]);
        $user = User::factory()->create([
            'role' => 'broker',
            'company_id' => $company->id,
        ]);
        $broker = Broker::find($user->id);

        $this->assertEquals('A random company name', $broker->getCompanyNameFromMetasOrFromCompanyRelationship());
    }

    /** @test */
    public function lender_getCompanyNameFromMetasOrFromCompanyRelationship_knows_about_company_name_using_company_relationship()
    {
        $company = Company::factory()->create([
            'company_name' => 'A random company name',
        ]);
        $user = User::factory()->create([
            'role' => 'lender',
            'company_id' => $company->id,
        ]);
        $lender = Lender::find($user->id);

        $this->assertEquals('A random company name', $lender->getCompanyNameFromMetasOrFromCompanyRelationship());
    }

    /** @test */
    public function getCompanyNameFromMetasOrFromCompanyRelationship_knows_about_company_name_using_company_relationship_when_metadata_is_blank()
    {
        $company = Company::factory()->create([
            'company_name' => 'A random company name',
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'metas' => [
                'company_data' => [
                    'company_name' => '',
                ]
            ]
        ]);

        $this->assertEquals('A random company name', $user->getCompanyNameFromMetasOrFromCompanyRelationship());
    }

    /** @test */
    public function getCompanyNameFromMetasOrFromCompanyRelationship_knows_about_company_name_using_company_domain_from_the_company_relationship_when_that_company_doesnt_have_a_name()
    {
        $company = Company::factory()->create([
            'company_name' => '',
            'domain' => 'example.com',
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'metas' => [
                'company_data' => [
                    'company_name' => '',
                ]
            ]
        ]);

        $this->assertEquals('Example', $user->getCompanyNameFromMetasOrFromCompanyRelationship());
    }
}
