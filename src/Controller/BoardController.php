<?php
declare(strict_types=1);

namespace App\Controller;


use App\Service\BoardService;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BoardController extends AbstractController
{
    /** @var BoardService  */
    private $boardService;

    public function __construct(BoardService $boardService)
    {
        $this->boardService = $boardService;
    }

    public function index(): Response
    {
        $mustache = new Mustache_Engine(['loader' => new Mustache_Loader_FilesystemLoader('../templates')]);
        $tpl = $mustache->loadTemplate('index');

        return new Response(
            $tpl->render(['milestones' => $this->boardService->getMilestones()])
        );
    }
}