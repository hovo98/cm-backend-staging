<?php

namespace Tests\Unit;

use Tests\TestCase;

class GlobalsTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function is_serialized(): void
    {
        $this->assertFalse(is_serialized('some data'));
    }
}
