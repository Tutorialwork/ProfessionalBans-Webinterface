<?php

namespace App\Controller\Api;

use App\Entity\Privatemessages;
use App\Repository\BansRepository;
use App\Repository\PrivatemessageRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BroadcastController extends AbstractController{

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
     * @Route("/api/chat/broadcast", name="api.chat.broadcast", methods={"POST"})
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
            $broadcast = new Privatemessages();
            $broadcast->setSender($token->getUuid());
            $broadcast->setReceiver("BROADCAST");
            $broadcast->setMessage($request->message);
            $broadcast->setStatus(0);
            $broadcast->setDate(time() * 1000);

            $em = $this->getDoctrine()->getManager();
            $em->persist($broadcast);
            $em->flush();

            return $this->json([
                'success' => 'Broadcast send',
            ]);
        } else {
            return $this->json([
                'error' => 'Invalid request',
            ], 400);
        }
    }

}