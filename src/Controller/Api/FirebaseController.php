<?php

namespace App\Controller\Api;

use App\Repository\BansRepository;
use App\Repository\ChatRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FirebaseController extends AbstractController{

    private $tokensRepository;
    private $bansRepository;

    public function __construct(TokensRepository $tokensRepository, BansRepository $bansRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/api/firebase", name="api.firebase", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }
        if(!array_key_exists("firebaseToken", $request)){
            return $this->json([
                'error' => 'Invalid request'
            ], 400);
        }

        $token->setFirebaseToken($request->firebaseToken);
        $token->setUpdatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->json([
            'success' => 'The firebasetoken was successfully for '.$token->getUuid().' updated',
        ]);
    }

}