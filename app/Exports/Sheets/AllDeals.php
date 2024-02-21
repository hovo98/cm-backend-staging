<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use App\DataTransferObjects\Deal\IndividualAllData;
use App\Deal;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AllDeals
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class AllDeals extends SheetAbstract implements SheetInterface
{
    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return Deal::query()->where('finished', true)->whereNull('deleted_at')->select('id');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'All Deals';
    }

    /**
     * @param  Model  $obj
     * @return array
     *
     * @throws Exception
     */
    public function map(Model $obj): array
    {
        $dealMapper = new IndividualAllData($obj->id);

        return collect($dealMapper->mapFromEloquent())->map([$this, 'mapNullToString'])->toArray();
    }

    /**
     * @return Collection
     */
    public function headings(): Collection
    {
        $dealMapper = new IndividualAllData();

        return collect($dealMapper->getHeadings());
    }
}
