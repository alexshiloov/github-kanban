<?php


namespace App\Tests\Unit\Service;


use App\Dto\IssueDto;
use App\Dto\MilestoneDto;
use App\Service\BoardService;
use App\Tests\MockDataTrait;
use Github\Api\Issue;
use Github\Api\Issue\Milestones;
use Github\Client;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class BoardServiceTest extends TestCase
{
    use MockDataTrait;

    private const NEWS_REPOSITORY = 'news-task';
    private const CALENDAR_REPOSITORY = 'calendar';

    private const GH_REPOSITORIES = [
        self::NEWS_REPOSITORY,
        self::CALENDAR_REPOSITORY,
    ];

    private const GH_ACCOUNT = 'test-user';

    /**
     * @dataProvider clientProvider
     */
    public function testGetMilestones(Client $client, LoggerInterface $logger): void
    {
        $service = new BoardService($client, self::GH_REPOSITORIES, self::GH_ACCOUNT, $logger);
        $this->assertMilestones(
            $service->getMilestones()
        );
    }

    public function clientProvider(): iterable
    {
        $milestonesMock = $this->createMock(Milestones::class);
        $milestonesMock
            ->method('all')
            ->withConsecutive([self::GH_ACCOUNT, self::NEWS_REPOSITORY], [self::GH_ACCOUNT, self::CALENDAR_REPOSITORY])
            ->willReturnOnConsecutiveCalls(
                self::getMockData('Service/Board/NewsRepository', 'getMilestones'),
                self::getMockData('Service/Board/CalendarRepository', 'getMilestones')
            )
        ;

        $issueMock = $this->createMock(Issue::class);
        $issueMock
            ->method('milestones')
            ->willReturn($milestonesMock)
        ;

        $issueMock
            ->method('all')
            ->withConsecutive(
                [self::GH_ACCOUNT, self::NEWS_REPOSITORY, ['state' => 'all', 'milestone' => 1]],
                [self::GH_ACCOUNT, self::NEWS_REPOSITORY, ['state' => 'all', 'milestone' => 2]],
                [self::GH_ACCOUNT, self::CALENDAR_REPOSITORY, ['state' => 'all', 'milestone' => 1]],
                [self::GH_ACCOUNT, self::CALENDAR_REPOSITORY, ['state' => 'all', 'milestone' => 2]]
            )
            ->willReturnOnConsecutiveCalls(
                self::getMockData('Service/Board/NewsRepository', 'getActiveIssues'),
                self::getMockData('Service/Board/NewsRepository', 'getQueueIssues'),
                self::getMockData('Service/Board/CalendarRepository', 'getClosedIssues'),
                self::getMockData('Service/Board/CalendarRepository', 'getQueueAndClosedIssues')
            )
        ;

        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->method('api')
            ->willReturn($issueMock)
        ;

        $logger = new TestLogger();

        return [
            [
                $clientMock,
                $logger,
            ]
        ];
    }

    /**
     * @param MilestoneDto[] $dtos
     */
    private function assertMilestones(array $dtos): void
    {
        $this->assertCount(4, $dtos, 'Count of milestones is no 4');
        foreach ($dtos as $dto) {
            $this->assertNotEmpty($dto->getName());
            $this->assertNotEmpty($dto->getUrl());
            $this->assertNotEmpty($dto->getProgress()->getPercent());

            $issueDtos = array_merge($dto->getActive(), $dto->getQueued(), $dto->getCompleted());
            foreach ($issueDtos as $issueDto) {
                $this->assertNotEmpty($issueDto->getId());
                $this->assertNotEmpty($issueDto->getNumber());
                $this->assertNotEmpty($issueDto->getTitle());
                $this->assertNotEmpty($issueDto->getUrl());
            }

            foreach ($dto->getActive() as $issueDto) {
                $this->assertNotEmpty($issueDto->getAssigneeUrl(), 'Active issue must have assignee');
            }

            foreach ($dto->getQueued() as $issueDto) {
                $this->assertEmpty($issueDto->getAssigneeUrl(), 'Queue issue must not have assignee');
            }
        }

        /** Milestone with active issues */
        $this->assertCount(3, $dtos[0]->getActive(), 'Count of active issues is not 3');
        $this->assertEmpty($dtos[0]->getCompleted(), 'Completed issues are not empty');
        $this->assertEmpty($dtos[0]->getQueued(), 'Queued issues are not empty');

        /** Check sorting in active issues */
        $this->assertEmpty($dtos[0]->getActive()[0]->getPausedLabels());
        $this->assertEmpty($dtos[0]->getActive()[1]->getPausedLabels());
        $this->assertLessThan(
            0,
            strcmp($dtos[0]->getActive()[0]->getTitle(), $dtos[0]->getActive()[1]->getTitle())
        );
        $this->assertEquals(IssueDto::PAUSED_LABELS, $dtos[0]->getActive()[2]->getPausedLabels());

        /** Milestone with queued issues */
        $this->assertCount(1, $dtos[1]->getQueued(), 'Count of queued issues is not 1');
        $this->assertEmpty($dtos[1]->getCompleted(), 'Completed issues are not empty');
        $this->assertEmpty($dtos[1]->getActive(), 'Active issues are not empty');

        /** Milestone with active issues */
        $this->assertCount(2, $dtos[2]->getCompleted(), 'Count of closed issues is not 2');
        $this->assertEmpty($dtos[2]->getQueued(), 'Queued issues are not empty');
        $this->assertEmpty($dtos[2]->getActive(), 'Active issues are not empty');

        /** Milestone with active and closed issues */
        $this->assertCount(1, $dtos[3]->getCompleted(), 'Count of closed issues is not 2');
        $this->assertCount(1, $dtos[3]->getQueued(), 'Count of queue issues is not 1');
        $this->assertEmpty($dtos[3]->getActive(), 'Active issues are not empty');
    }
}