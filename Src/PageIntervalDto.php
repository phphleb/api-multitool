<?php
declare(strict_types = 1);

/** @author Foma Tuturov */

namespace Phphleb\ApiMultitool\Src;

/**
 * DTO для содержания данных о сортировке на странице.
 *
 * При запросе неустановленных данных выведет ошибку типизации.
 * При попытке перезаписать установленные данные вернёт ошибку.
 */
final class PageIntervalDto
{
    private ?int $page = null;

    private ?int $count = null;

    private ?int $startInterval = null;

    private ?int $endInterval = null;

    /** Возвращает номер начальной позиции ресурса. */
    public function getStart(): int
    {
        return $this->startInterval;
    }

    /** Аналог getStart() */
    public function getOffset(): int
    {
        return $this->startInterval;
    }

    /*  Устанавливает номер начальной позиции ресурса. */
    public function setStart(int $startInterval): void
    {
        if (!is_null($this->startInterval)) {
            throw new \ErrorException('Value `start` already set');
        }
        $this->startInterval = $startInterval;
    }

    /** Возвращает номер конечной позиции ресурса. */
    public function getEnd(): int
    {
        return $this->endInterval;
    }

    /*  Устанавливает номер конечной позиции ресурса. */
    public function setEnd(int $endInterval): void
    {
        if (!is_null($this->endInterval)) {
            throw new \ErrorException('Value `end` already set');
        }
        $this->endInterval = $endInterval;
    }

    /** Возвращает номер страницы. */
    public function getPage(): int
    {
        return $this->page;
    }

    /* Устанавливает номер страницы */
    public function setPage(int $page): void
    {
        if (!is_null($this->page)) {
            throw new \ErrorException('Value `page` already set');
        }
        $this->page = $page;
    }

    /** Возвращает количество выводимых ресурсов на одной странице. */
    public function getCount(): int
    {
        return $this->count;
    }

    /** Аналог getCount(). */
    public function getLimit(): int
    {
        return $this->count;
    }

    /* Устанавливает количество выводимых ресурсов на одной странице. */
    public function setCount(int $count): void
    {
        if (!is_null($this->count)) {
            throw new \ErrorException('Value `count` already set');
        }
        $this->count = $count;
    }

}

