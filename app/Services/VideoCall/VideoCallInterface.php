<?php

namespace App\Services\VideoCall;

interface VideoCallInterface
{
    public function createRoomUrl(string $agenda): void;

    public function getJoinUrl(): string;

    public function getStartUrl(): string;
}
