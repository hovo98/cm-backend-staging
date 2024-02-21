<?php

namespace App\DataTransferObjects\Interfaces;

interface JsonbMapper
{
    public function mapToEloquent(array $args, \Illuminate\Foundation\Auth\User $user);

    public function mapFromEloquent();

    public function appendInducted($data);

    public static function mapQuery($paginator, $perPage, $currentPage);

    public function dataUnit();

    public function stringUnit();

    public function arrayUnit();

    public function enumUnit();

    public function numberUnit();

    public function booleanUnit();
}
