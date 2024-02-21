<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Class LoanSize
 *
 * Contains the min and max of dollar amount for the Loan Size of the Lender's Deal Preferences
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class LoanSize implements \ArrayAccess
{
    /**
     * @var int
     */
    public $min;

    /**
     * @var int
     */
    public $max;

    /**
     * LoanSize constructor.
     *
     * @param  int  $min
     * @param  int  $max
     */
    public function __construct(int $min, int $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @return int
     */
    public function min(): int
    {
        return $this->min;
    }

    /**
     * @return string
     */
    public function formattedMin(): string
    {
        return number_format($this->min);
    }

    /**
     * @return int
     */
    public function max(): int
    {
        return $this->max;
    }

    /**
     * @return string
     */
    public function formattedMax(): string
    {
        return number_format($this->max);
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
