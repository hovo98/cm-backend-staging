<?php

namespace App\Interfaces;

interface TypeService
{
    // TODO IMPROVEMENT abstract fmap in own intf extended, use ac Trait in abstract Service in order to chane types in
    // fmap toAbstractService
    // TODO implement Type
    public function fmap(QueryService $queryService, $mapperService, array $commands = []);
}
