<?php

namespace App\Controller\Api;

use App\Repository\BansRepository;
use App\Repository\ChatRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController{

    private $tokensRepository;
    private $bansRepository;
    private $chatRepository;

    public function __construct(TokensRepository $tokensRepository, BansRepository $bansRepository, ChatRepository $chatRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->bansRepository = $bansRepository;
        $this->chatRepository = $chatRepository;
    }

    /**
     * @Route("/api/chat", name="api.chat", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        $chats = $this->chatRepository->findBy([''], ['Senddate' => 'DESC']);

        foreach ($chats as $chat){
            $uuid = $this->bansRepository->findOneBy(['UUID' => $chat->getUUID()]);
            $chat->setUUID($uuid->getName());
        }

        return $this->json([
            'chats' => $chats,
        ]);
    }

}