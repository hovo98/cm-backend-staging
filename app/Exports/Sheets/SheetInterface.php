<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * Interface SheetInterface
 */
interface SheetInterface
{
    public function title(): string;

    public function map(Model $obj): array;

    public function headings(): Collection;

    public function count(): int;

    public function query(): Builder;

    public function camelTitle(): string;

    public function prepareChunk(array $manifest): LazyCollection;

    public function mapNullToString($element);
}
