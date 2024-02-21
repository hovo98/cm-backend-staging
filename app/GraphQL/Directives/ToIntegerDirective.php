<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;

/**
 * Class ToIntegerDirective
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
final class ToIntegerDirective extends BaseDirective implements ArgDirective, ArgTransformerDirective
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
directive @toInteger on INPUT_FIELD_DEFINITION
GRAPHQL;
    }

    /**
     * Apply transformations on the value of an argument given to a field.
     *
     * @param  mixed  $argumentValue
     * @return mixed
     */
    public function transform($argumentValue): ?int
    {
        return $argumentValue ? (int)str_replace(',', '', $argumentValue) : null;
    }
}
