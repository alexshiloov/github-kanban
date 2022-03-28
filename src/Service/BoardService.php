<?php
declare(strict_types=1);

namespace App\Service;


use App\Dto\IssueDto;
use App\Dto\MilestoneDto;
use App\Dto\StatisticsDto;
use Exception;
use Github\Api\Issue;
use Github\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class BoardService
{
    private const CLOSED_STATE = 'closed';

    /** @var Client */
    private $ghClient;

    /** @var string[] */
    private $ghRepositories = [];

    /** @var string */
    private $ghAccount;

    private $logger;

    public function __construct(Client $client, array $ghRepositories, string $ghAccount, LoggerInterface $logger)
    {
        $this->ghClient = $client;
        $this->ghRepositories = $ghRepositories;
        $this->ghAccount = $ghAccount;
        $this->logger = $logger;
    }

    /**
     * @return MilestoneDto[]
     */
    public function getMilestones(): array
    {
        /** @var Issue $issuesApi */
        $issuesApi = $this->ghClient->api('issues');

        $milestoneDtos = [];
        foreach ($this->ghRepositories as $repository) {
            try {
                $milestones = $issuesApi->milestones()->all($this->ghAccount, $repository);
            } catch (Exception $e) {
                if ($e->getCode() !== Response::HTTP_NOT_FOUND) {
                    throw $e;
                }

                $this->logger->info(sprintf('Repository %s does not contains any milestones', $repository));
                continue;
            }

            foreach ($milestones as $milestoneDatum) {
                $dto = $this->toMilestoneDto($milestoneDatum);

                $issues = $issuesApi->all(
                    $this->ghAccount,
                    $repository,
                    ['state' => 'all', 'milestone' => $milestoneDatum['number']]
                );

                foreach ($issues as $issueDatum) {
                    if (isset($issueDatum['pull_request'])) {
                        continue;
                    }

                    $issueDto = new IssueDto($issueDatum);
                    if ($issueDatum['state'] === self::CLOSED_STATE) {
                        $dto->addCompleted($issueDto);
                        continue;
                    }

                    if ($issueDatum['assignee']) {
                        $dto->addActive($issueDto);
                    } else {
                        $dto->addQueued($issueDto);
                    }
                }

                $this->sortActiveIssues($dto);

                $milestoneDtos[] = $dto;
            }
        }

        return $milestoneDtos;
    }

    private function toMilestoneDto(array $data): MilestoneDto
    {
        $dto = (new MilestoneDto())
            ->setName($data['title'])
            ->setUrl($data['html_url'])
        ;

        if ($data['open_issues'] || $data['closed_issues']) {
            $dto->setProgress(
                new StatisticsDto($data['closed_issues'], $data['open_issues'])
            );
        }

        return $dto;
    }

    private function sortActiveIssues(MilestoneDto $dto): void
    {
        /** Sorting active issues */
        $activeIssueDtos = $dto->getActive();
        usort( $activeIssueDtos, function (IssueDto $a, IssueDto $b) {
            return count($a->getPausedLabels()) - count($b->getPausedLabels()) === 0
                ? strcmp($a->getTitle(), $b->getTitle())
                : count($a->getPausedLabels()) - count($b->getPausedLabels())
            ;
        });

        $dto->setActive($activeIssueDtos);
    }
}