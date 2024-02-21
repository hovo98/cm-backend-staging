<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Broker\Quotes;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;
use Illuminate\Foundation\Auth\User;

/**
 * Class AllQuotes
 */
class AllQuotes implements TypeService
{
    /* @var User */
    protected $broker;

    /* @var string */
    protected $searchLocation;

    /* @var array */
    protected $sponsorNames;

    /* @var string */
    protected $sponsorName;

    /* @var string */
    protected $sortBy;

    /* @var array */
    protected $tags;

    /* @var int */
    protected $currentPage;

    /* @var int */
    protected $perPage;

    /**
     * AllQuotes constructor.
     *
     * @param  User  $broker
     * @param  string  $searchLocation
     * @param  array  $sponsorNames
     * @param  string  $sponsorName
     * @param  array  $sortBy
     * @param  array  $tags
     * @param  int  $currentPage
     * @param  int  $perPage
     */
    public function __construct(User $broker, string $searchLocation, array $sponsorNames, string $sponsorName, array $sortBy, array $tags, int $currentPage, int $perPage)
    {
        $this->broker = $broker;
        $this->searchLocation = $searchLocation;
        $this->sponsorNames = $sponsorNames;
        $this->sponsorName = $sponsorName;
        $this->sortBy = $sortBy;
        $this->tags = $tags;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'broker' => $this->broker,
                'searchLocation' => $this->searchLocation,
                'sponsorNames' => $this->sponsorNames,
                'sponsorName' => $this->sponsorName,
                'sortBy' => $this->sortBy,
                'tags' => $this->tags,
                'currentPage' => $this->currentPage,
                'perPage' => $this->perPage,
            ]
        );

        return $mapperService->map($data);
    }
}
