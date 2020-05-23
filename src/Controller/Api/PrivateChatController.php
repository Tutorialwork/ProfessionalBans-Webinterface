<?php

namespace App\Controller\Api;

use App\Entity\Privatemessages;
use App\Repository\BansRepository;
use App\Repository\PrivatemessageRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PrivateChatController extends AbstractController{

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
     * @Route("/api/chat/private", name="api.chat.private", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        if(array_key_exists("to", $request) && array_key_exists("message", $request)){
            $targetUuid = $this->bansRepository->findOneBy(['Name' => $request->to]);
            if(!$targetUuid){
                return $this->json([
                    'error' => 'Player not found'
                ], 404);
            }

            $message = new Privatemessages();
            $message->setSender($token->getUuid());
            $message->setReceiver($targetUuid->getUUID());
            $message->setMessage($request->message);
            $message->setStatus(0);
            $message->setDate(time() * 1000);

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            return $this->json([
                'success' => 'Message send',
            ]);
        } else {
            $chats = $this->privatemessageRepository->findChatFrom($token->getUuid());

            foreach ($chats as $chat){
                $uuid = $this->bansRepository->findOneBy(['UUID' => $chat->getSender()]);
                $rUuid = $this->bansRepository->findOneBy(['UUID' => $chat->getReceiver()]);
                $chat->setSender($uuid->getName());
                $chat->setReceiver($rUuid->getName());
            }

            return $this->json([
                'privatechats' => $chats,
            ]);
        }
    }

}