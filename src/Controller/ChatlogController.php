<?php

namespace App\Controller;

use App\Repository\BansRepository;
use App\Repository\ChatlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/chatlogs", name="chatlog.")
 */
class ChatlogController extends AbstractController
{

    private $chatlogRepository;
    private $bansRepository;

    public function __construct(ChatlogRepository $chatlogRepository, BansRepository $bansRepository)
    {
        $this->chatlogRepository = $chatlogRepository;
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $chatlogs = $this->chatlogRepository->findAll();
        $chatlogs_output = array();

        foreach ($chatlogs as $chatlog) {
            $chatId = $chatlog->getLogId();
            if(!array_key_exists($chatId, $chatlogs_output)){
                $chatlogs_output[$chatId] = $chatlog;
            }

            $reportedUuid = $this->bansRepository->findOneBy(['UUID' => $chatlog->getUuid()]);
            $creatorUuid = $this->bansRepository->findOneBy(['UUID' => $chatlog->getCreatorUuid()]);

            $chatlog->setUuid($reportedUuid->getName());
            $chatlog->setCreatorUuid($creatorUuid->getName());
            $chatlog->setCreatedAt(round($chatlog->getCreatedAt() / 1000));
        }

        return $this->render('chatlog/index.html.twig', [
            'chatlogs' => $chatlogs_output,
        ]);
    }
}
