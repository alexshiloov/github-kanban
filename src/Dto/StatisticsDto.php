<?php
declare(strict_types=1);

namespace App\Dto;


class StatisticsDto
{
    /** @var int */
    private $total;

    /** @var int */
    private $complete;

    /** @var int */
    private $remaining;

    /** @var int */
    private $percent;

    public function __construct(int $complete, int $remaining)
    {
        $this->complete = $complete;
        $this->remaining = $remaining;
        $this->total = $complete + $remaining;
        $this->percent = intval(round($this->complete / $this->total * 100));
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getComplete(): int
    {
        return $this->complete;
    }

    public function getRemaining(): int
    {
        return $this->remaining;
    }

    public function getPercent(): int
    {
        return $this->percent;
    }
}