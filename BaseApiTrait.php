<?php
declare(strict_types = 1);

/** @author Foma Tuturov */

namespace Phphleb\ApiMultitool;

use Phphleb\ApiMultitool\Src\{
    ApiHandlerTrait, ApiMethodWrapperTrait, ApiPageManagerTrait, ApiRequestDataManagerTrait
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
    use ApiHandlerTrait;
    use ApiRequestDataManagerTrait;
    use ApiPageManagerTrait;
}

