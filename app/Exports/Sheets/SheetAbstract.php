<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use App\Exports\AllDataExporter;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

/**
 * Class SheetAbstract
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
abstract class SheetAbstract implements SheetInterface
{
    /** @var int */
    private $count;

    /** @var array */
    protected $manifest;

    public function prepareChunk(array $manifest): LazyCollection
    {
        $this->manifest = $manifest;

        return $this
            ->query()
            ->forPage($manifest['chunksProcessed'] + 1, AllDataExporter::CHUNK_SIZE)
            ->cursor()
            ->map([$this, 'map']);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        if ($this->count) {
            return $this->count;
        }

        $this->count = $this->query()->count();

        return $this->count;
    }

    public function camelTitle(): string
    {
        return Str::camel($this->title());
    }

    public function mapNullToString($element)
    {
        return $element === null ? '' : $element;
    }
}
