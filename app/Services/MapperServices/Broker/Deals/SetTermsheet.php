<?php

namespace App\Services\MapperServices\Broker\Deals;

class SetTermsheet
{
    public function map($status)
    {
        return [
            'status' => $status,
        ];
    }
}
