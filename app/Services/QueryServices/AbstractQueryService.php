<?php

declare(strict_types=1);

namespace App\Services\QueryServices;

use App\Interfaces\QueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class AbstractQueryService
 *
 * @author  Vladislav Mosnak <vlada@forwardslashny.com>
 */
abstract class AbstractQueryService implements QueryService
{
    /** @var int */
    protected $currentPage = 1;

    /** @var int */
    protected $perPage = 10;

    /**
     * @param  int  $perPage
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * @param  int  $currentPage
     */
    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @param  Builder  $query
     * @return array
     */
    public function paginate(Builder $query): array
    {
        $total = $query->cursor()->unique('id')->count();
        $items = $query->cursor()->unique('id')->skip(($this->currentPage - 1) * $this->perPage)->take($this->perPage);

        $items = collect($items);

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $this->perPage,
            $this->currentPage
        );

        return [
            'data' => $items,
            'paginatorInfo' => [
                'count' => $paginator->count(),
                'currentPage' => $paginator->currentPage(),
                'firstItem' => $paginator->firstItem(),
                'hasMorePages' => $paginator->hasMorePages(),
                'lastItem' => $paginator->lastItem(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
