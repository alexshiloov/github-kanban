<?php
declare(strict_types=1);

namespace App\Dto;


class IssueDto
{
    public const PAUSED_LABELS = ['waiting-for-feedback'];

    /** @var int */
    private $id;

    /** @var int */
    private $number;

    /** @var string */
    private $title;

    /** @var string */
    private $url;

    /** @var string|null */
    private $assigneeUrl;

    /** @var string[] */
    private $pausedLabels = [];

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->number = $data['number'];
        $this->title = $data['title'];
        $this->url = $data['html_url'];
        $this->assigneeUrl = $data['assignee'] ? $data['assignee']['avatar_url'] : null;

        foreach ($data['labels'] as $label) {
            if (in_array($label['name'], self::PAUSED_LABELS)) {
                $this->pausedLabels[] = $label['name'];
            }
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAssigneeUrl(): ?string
    {
        return $this->assigneeUrl;
    }

    public function getPausedLabels(): array
    {
        return $this->pausedLabels;
    }
}