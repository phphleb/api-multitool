<?php
declare(strict_types = 1);

/** @author Foma Tuturov */

namespace Phphleb\ApiMultitool;

use Phphleb\ApiMultitool\Src\{
    ApiHandlersTrait, ApiMethodWrapperTrait, ApiPageManagerTrait, ApiRequestDataManagerTrait
};

/**
 * Набор трейтов для работы с входящими данными API.
 *
 * Эти трейты независимы друг от друга и могут быть использованы
 * также в любой их комбинации.
 */
trait BaseApiTrait
{
    use ApiMethodWrapperTrait;
    use ApiHandlersTrait;
    use ApiRequestDataManagerTrait;
    use ApiPageManagerTrait;
}

