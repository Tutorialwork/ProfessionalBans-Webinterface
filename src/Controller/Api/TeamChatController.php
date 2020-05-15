<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\Privatemessages;
use App\Repository\BansRepository;
use App\Repository\PrivatemessageRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TeamChatController extends AbstractController{

    private $tokensRepository;
    private $bansRepository;
    private $privatemessageRepository;

    public function __construct(TokensRepository $tokensRepository, BansRepository $bansRepository, PrivatemessageRepository $privatemessageRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->bansRepository = $bansRepository;
        $this->privatemessageRepository = $privatemessageRepository;
    }

    /**
     * @Route("/api/chat/team", name="api.chat.team", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        if(array_key_exists("message", $request)){
            $teamChatMessage = new Privatemessages();
            $teamChatMessage->setSender($token->getUuid());
            $teamChatMessage->setReceiver("TEAM");
            $teamChatMessage->setMessage($request->message);
            $teamChatMessage->setStatus(0);
            $teamChatMessage->setDate(time() * 1000);

            $em = $this->getDoctrine()->getManager();
            $em->persist($teamChatMessage);
            $em->flush();

            return $this->json([
                'success' => 'Message was send',
            ]);
        } else {
            $chats = $this->privatemessageRepository->findBy(['Receiver' => 'TEAM'], ['Date' => 'DESC']);

            foreach ($chats as $chat){
                $uuid = $this->bansRepository->findOneBy(['UUID' => $chat->getSender()]);
                $rUuid = $this->bansRepository->findOneBy(['UUID' => $chat->getReceiver()]);
                $chat->setSender($uuid->getName());
                $chat->setReceiver($rUuid->getName());
            }

            return $this->json([
                'teamchat' => $chats,
            ]);
        }
    }

}