<?php
declare(strict_types = 1);

/** @author Foma Tuturov */

namespace Phphleb\ApiMultitool\Src;

/**
 * Трейт для обработки параметров страницы, таких как номер страницы и кол-во выводимых ресурсов,
 * чтобы представить их в удобном для дальнейших расчётов виде.
 */
trait ApiPageManagerTrait
{
    /** @internal */
    private int $apiBoxMaxCountPerPage = 20;

    /**
     * Возвращает максимальное значение для кол-ва ресурсов на странице.
     */
    protected function getApiBoxMaxCountPerPage(): int
    {
        return $this->apiBoxMaxCountPerPage;
    }

    /**
     * Устанавливает максимальное значение для кол-ва ресурсов на странице.
     */
    protected function setApiBoxMaxCountPerPage(int $count): void
    {
        $this->apiBoxMaxCountPerPage = $count;
    }

    /**
     * Возвращает DTO-объект с вычисленными данными страницы.
     *
     * @param int $page - номер страницы (0 и 1 равнозначны 1).
     * @param int $count - количество выводимых ресурсов на странице. Зависит от переданного `allCount`,
     *                          при этом определяется состав последней (возможно неполной) страницы.
     * @param int|null $allCount - максимальное кол-во ресурсов.
     *
     * @return PageIntervalDto - возвращается класс с установленными значениями страницы.
     * @throws \ErrorException
     */
    protected function getPageInterval(int $page = 0, int $count = 0, ?int $allCount = null): PageIntervalDto
    {
        $count = $count && $count <= $this->apiBoxMaxCountPerPage ? $count : $this->apiBoxMaxCountPerPage;
        $count = abs($count);
        $page = $page ?: 1;
        $page = abs($page);
        $dto = new PageIntervalDto();
        $dto->setPage($page);
        $dto->setCount($count);
        $start = $page > 1 ? ($page - 1 ) * $count : 0;
        if (is_null($allCount)) {
            $dto->setStart($start);
        } else {
            $dto->setStart($start > $allCount ? $allCount : $start);
        }
        if (is_null($allCount)) {
            $dto->setEnd($start + $count);
        } else {
            $dto->setEnd($start + $count > $allCount ? $allCount : $start + $count);
        }

        return $dto;
    }

}

