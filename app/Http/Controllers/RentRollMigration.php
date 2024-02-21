<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class RentRollMigration
 *
 * @author Nikola Popov
 */
class RentRollMigration extends Controller
{
    /**
     * @var Collection
     */
    private $deals;

    /**
     * Change structure for other expenses field
     */
    public function updateRentRoll()
    {
        $this
            ->loadAllDeals()
            ->updateData();
    }

    /**
     * Get all deals
     *
     * @return $this
     */
    private function loadAllDeals()
    {
        $this->deals = Deal::all();

        return $this;
    }

    /**
     * @return $this
     */
    private function updateData()
    {
        $this->deals->each(function (Deal $deal) {
            $mapper = $this->getMapper($deal);
            $mappedDeal = $this->getMappedDeal($mapper);
            $user = $this->getUser($deal);

            $table = $mappedDeal['rent_roll']['table'];
            $newTable = [];
            $annualRentSFTotal = 0;

            foreach ($table as $obj) {
                $obj['occupied'] = true;

                $removeComma = str_replace(',', '', $obj['sf']);
                $sfInt = str_replace(' ', '', $removeComma);

                if (is_numeric($sfInt)) {
                    $sf = (int) str_replace(',', '', $obj['sf']);
                    if (is_numeric($obj['annual_rent'])) {
                        $annualRent = (float) str_replace(',', '', $obj['annual_rent']);
                        if ($sf > 0) {
                            $sum = $annualRent / $sf;
                            $annualRentSFTotal += $sum;
                            $obj['annual_rent_sf'] = (string) number_format($sum, 2);
                        } else {
                            $obj['annual_rent_sf'] = '';
                        }
                    } else {
                        $obj['annual_rent_sf'] = '';
                    }
                } else {
                    if (isset($obj['annual_rent_sf'])) {
                        if (is_numeric($obj['annual_rent_sf'])) {
                            $annualRentSFTotal += (int) $obj['annual_rent_sf'];
                        }
                    } else {
                        $obj['annual_rent_sf'] = '';
                    }
                }

                $obj['lease_start'] = $this->checkIsItDate($obj['lease_start']);
                $obj['lease_end'] = $this->checkIsItDate($obj['lease_end']);

                $newTable[] = $obj;
            }

            $mappedDeal['rent_roll']['table'] = $newTable;
            $mappedDeal['rent_roll']['vacancy'] = '';
            if ($annualRentSFTotal > 0) {
                $mappedDeal['rent_roll']['annual_sf_total'] = number_format($annualRentSFTotal, 2);
            } else {
                $mappedDeal['rent_roll']['annual_sf_total'] = '';
            }
            $mappedDeal['rent_roll']['occupiedGroos'] = '';

            if (isset($mappedDeal['lastStepStatus'])) {
                unset($mappedDeal['lastStepStatus']);
            }

            $this->persistData($mapper, $mappedDeal, $user);
        });

        return $this;
    }

    private function checkIsItDate($val)
    {
        $arrOfInt = explode('-', $val);
        if (count($arrOfInt) === 3) {
            if (Carbon::createFromFormat('Y-m-d', $val)->format('Y-m-d') === $val) {
                return $arrOfInt[1].'/'.$arrOfInt[2].'/'.$arrOfInt[0];
            }
        }

        $arrOfInt1 = explode('/', $val);
        if (count($arrOfInt1) === 3) {
            if (Carbon::createFromFormat('Y/m/d', $val)->format('Y/m/d') === $val) {
                return $arrOfInt1[1].'/'.$arrOfInt1[2].'/'.$arrOfInt1[0];
            }
        }

        return $val;
    }

    private function getMapper(Deal $deal)
    {
        try {
            return new DealMapper($deal->id);
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            throw $e;
        }
    }

    private function getMappedDeal($mapper)
    {
        return $mapper->mapFromEloquent();
    }

    private function getUser(Deal $deal)
    {
        return User::find($deal->user_id);
    }

    /**
     * Save changed structure of field
     *
     * @return $this
     */
    private function persistData(DealMapper $mapper, array $mappedDeal, \Illuminate\Foundation\Auth\User $user)
    {
        $deal = $mapper->mapToEloquent($mappedDeal, $user);
        $deal->save();

        return $this;
    }
}
