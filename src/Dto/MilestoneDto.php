<?php
declare(strict_types=1);

namespace App\Dto;


class MilestoneDto
{
    /** @var string */
    private $name;

    /** @var string */
    private $url;

    /** @var StatisticsDto|null */
    private $progress;

    /** @var IssueDto[] */
    private $queued = [];

    /** @var IssueDto[] */
    private $active = [];

    /** @var IssueDto[] */
    private $completed = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getProgress(): ?StatisticsDto
    {
        return $this->progress;
    }

    public function setProgress(StatisticsDto $progress): self
    {
        $this->progress = $progress;
        return $this;
    }

    public function getQueued(): array
    {
        return $this->queued;
    }

    public function addQueued(IssueDto $dto): self
    {
        $this->queued[] = $dto;
        return $this;
    }

    public function getActive(): array
    {
        return $this->active;
    }

    public function addActive(IssueDto $dto): self
    {
        $this->active[] = $dto;
        return $this;
    }

    public function getCompleted(): array
    {
        return $this->completed;
    }

    public function addCompleted(IssueDto $dto): self
    {
        $this->completed[] = $dto;
        return $this;
    }

    /**
     * @param IssueDto[] $active
     * @return $this
     */
    public function setActive(array $active): self
    {
        $this->active = $active;
        return $this;
    }
}