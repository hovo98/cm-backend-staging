<?php

namespace App\Services\MapperServices\Broker\Deals;

class StoreDeal implements \App\Interfaces\IndividualMapperService
{
    // TODO not in use, finish implementation or remove
    public function map($obj)
    {
        $mapper = new \App\DataTransferObjects\DealMapper($obj);

        return  $mapper->mapFromEloquent();
    }
}
