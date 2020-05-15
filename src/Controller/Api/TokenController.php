<?php

namespace App\Controller\Api;

use App\Repository\BansRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TokenController extends AbstractController{

    private $tokensRepository;
    private $bansRepository;

    public function __construct(TokensRepository $tokensRepository, BansRepository $bansRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/api/token", name="api.token", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        $ban = $this->bansRepository->findOneBy(['UUID' => $token->getUuid()]);

        return $this->json([
            'username' => $ban->getName(),
            'token' => $token
        ]);
    }

}