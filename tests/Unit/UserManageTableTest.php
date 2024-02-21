<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\User;
use Tests\TestCase;

class UserManageTableTest extends TestCase
{
    public function testSetManageTable()
    {
        $user = User::factory()->create(['role' => 'broker']);

        $manageTable = [
            'location',
            'updated_at',
            'property_type',
            'sensitivity',
        ];
        $table = 'deals';

        $result = $user->updateManageTable($manageTable, $table);

        $this->assertTrue(is_array($result) && in_array('location', $result) && in_array('updated_at', $result)
            && in_array('property_type', $result) && in_array('sensitivity', $result));
    }

    public function testGetManageTable()
    {
        $user = User::factory()->create(['role' => 'broker']);

        $manageTable = [
            'location',
            'updated_at',
            'property_type',
            'sensitivity',
        ];
        $table = 'deals';

        $user->updateManageTable($manageTable, $table);

        $result = $user->getManageTable($table);

        $this->assertTrue(is_array($result) && in_array('location', $result) && in_array('updated_at', $result)
            && in_array('property_type', $result) && in_array('sensitivity', $result));
    }
}
