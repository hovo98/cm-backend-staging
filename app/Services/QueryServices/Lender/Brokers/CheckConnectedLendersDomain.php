<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Brokers;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class CheckConnectedLendersDomain
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CheckConnectedLendersDomain extends AbstractQueryService
{
    /**
     * @var BrokersConnectedToMultipleLenders
     */
    private $brokersConnectedToMultipleLenders;

    /**
     * CheckConnectedLendersDomain constructor.
     *
     * @param  BrokersConnectedToMultipleLenders  $brokersConnectedToMultipleLenders
     */
    public function __construct(BrokersConnectedToMultipleLenders $brokersConnectedToMultipleLenders)
    {
        $this->brokersConnectedToMultipleLenders = $brokersConnectedToMultipleLenders;
    }

    /**
     * Returns the IDs of the connected brokers to his colleagues
     *
     * @param  array  $args id => int, domain => string
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['domain'])->get()->pluck('id');
    }

    /**
     * Returns full Builder query for the IDs of the connected brokers to his colleagues
     *
     * @param  string  $domain
     * @return Builder
     */
    public function query(string $domain): Builder
    {
        $colleagues = DB::table('users')
            ->select('id')
            ->where('role', '=', 'lender')
            ->where('email', 'LIKE', "%@${domain}")
            ->whereNull('deleted_at');

        //brokersConnectedToColleagues
        return $this->brokersConnectedToMultipleLenders->query($colleagues);
    }
}
