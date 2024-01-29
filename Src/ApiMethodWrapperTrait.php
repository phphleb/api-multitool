<?php
declare(strict_types = 1);

/** @author Foma Tuturov */

namespace Phphleb\ApiMultitool\Src;

/**
 * Трейт для стандартизации вывода(предположительно API), при котором
 * возникающие ошибки обрабатываются пользовательским методом.
 * Требует присутствия метода обработки ошибок `error`,
 * из трейта @see \Phphleb\ApiMultitool\Src\ApiHandlerTrait
 * или собственной реализации метода, созданной специально для этой цели.
 *
 */
trait ApiMethodWrapperTrait
{
    /**
     * Перехват вызовов и обёртка ошибок в стандартный ответ API.
     * Действует для методов наследующихся контроллеров,
     * начинающихся с `action`, например, `actionGetUser`,
     * тогда как в роутинге обращение идёт к `getUser` контроллера.
     * Внимание! Вызывает метод `error` для обработки ошибок.
     */
    public function __call(string $method_name, array $arguments)
    {
        try {
            if (method_exists($this, 'action' . ucfirst($method_name))) {
                return call_user_func([$this, 'action' . ucfirst($method_name)], $arguments);
            }
            if (!method_exists($this, $method_name)) {
                return $this->error('404 Not Found', 404);
            }
        } catch (\Throwable $exception) {
            return $this->error($exception);
        }
    }
}

