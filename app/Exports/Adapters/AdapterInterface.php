<?php

declare(strict_types=1);

namespace App\Exports\Adapters;

/**
 * Interface AdapterInterface
 */
interface AdapterInterface
{
    public function appendRows(string $sheetName, array $data): self;

    public function emptySheet(array $manifest): self;
}
