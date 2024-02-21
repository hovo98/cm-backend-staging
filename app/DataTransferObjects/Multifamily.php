<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Class Multifamily
 *
 * Contains the min and max of dollar amount for the Multifamily asset type of the Lender's Deal Preferences
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class Multifamily implements \ArrayAccess
{
    /**
     * @var int
     */
    public $min_amount;

    /**
     * @var int
     */
    public $max_amount;

    /**
     * LoanSize constructor.
     *
     * @param  int  $min_amount
     * @param  int  $max_amount
     */
    public function __construct(int $min_amount, int $max_amount)
    {
        $this->min_amount = $min_amount;
        $this->max_amount = $max_amount;
    }

    /**
     * @return int
     */
    public function min_amount(): int
    {
        return $this->min_amount;
    }

    /**
     * @return int
     */
    public function max_amount(): int
    {
        return $this->max_amount;
    }

    public function offsetExists($offset)
    {
        return method_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset();
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}
