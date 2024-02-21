<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use App\DataTransferObjects\Quote\IndividualAllData;
use App\Quote;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AllQuotes
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class AllQuotes extends SheetAbstract implements SheetInterface
{
    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return Quote::query()->where('finished', true)->withTrashed();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'All Quotes';
    }

    /**
     * @param  Quote  $obj
     * @return array
     *
     * @throws Exception
     */
    public function map(Model $obj): array
    {
        $quoteMapper = new IndividualAllData($obj->id);

        return collect($quoteMapper->getMappedDataExel($obj->id))
            ->map([$this, 'mapNullToString'])
            ->toArray();
    }

    public function headings(): Collection
    {
        $quoteMapper = new IndividualAllData();

        return collect($quoteMapper->getHeadings());
    }
}
