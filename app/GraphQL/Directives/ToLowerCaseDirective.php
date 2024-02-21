<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;

/**
 * Class ToLowerCaseDirective
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
final class ToLowerCaseDirective extends BaseDirective implements ArgDirective, ArgTransformerDirective
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
directive @toLowerCase on INPUT_FIELD_DEFINITION
GRAPHQL;
    }

    /**
     * Apply transformations on the value of an argument given to a field.
     *
     * @param  mixed  $argumentValue
     * @return mixed
     */
    public function transform($argumentValue): ?string
    {
        if (! $argumentValue) {
            return null;
        }

        return strtolower($argumentValue);
    }
}
