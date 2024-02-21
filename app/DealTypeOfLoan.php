<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DealTypeOfLoan
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class DealTypeOfLoan extends Model
{
    use HasFactory;

    public const DEAL_TYPE_OF_LOAN = [
        1 => 'Hard Money',
        2 => 'Agency',
        3 => 'CMBS',
        4 => 'Balance Sheet',
    ];

    /**
     *  Deal has multiple types of loan and is stored is in deal_loan_type table
     */
    protected $table = 'deal_type_of_loan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'deal_id', 'type_of_loan',
    ];
}
