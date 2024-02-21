<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Broker\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;
use Illuminate\Foundation\Auth\User;

/**
 * Class FilterDealsBroker
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterDealsBroker implements TypeService
{
    public $user;

    public $context;

    public $searchTerms;

    public $loanSize;

    public $assetTypes;

    public $sortBy;

    public $tags;

    public $perPage;

    public $currentPage;

    public function __construct(
        User $user,
        string $context,
        string $searchTerms,
        array $loanSize,
        array $assetTypes,
        array $sortBy,
        array $tags,
        int $perPage,
        int $currentPage
    ) {
        $this->user = $user;
        $this->context = $context;
        $this->searchTerms = $searchTerms;
        $this->loanSize = $loanSize;
        $this->assetTypes = $assetTypes;
        $this->sortBy = $sortBy;
        $this->tags = $tags;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'user' => $this->user,
                'context' => $this->context,
                'searchTerms' => $this->searchTerms,
                'loanSize' => $this->loanSize,
                'assetTypes' => $this->assetTypes,
                'sortBy' => $this->sortBy,
                'tags' => $this->tags,
                'perPage' => $this->perPage,
                'currentPage' => $this->currentPage,
            ]
        );

        return $mapperService->map($data);
    }
}
