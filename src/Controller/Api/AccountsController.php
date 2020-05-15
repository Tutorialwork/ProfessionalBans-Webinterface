<?php

namespace App\Controller\Api;

use App\Repository\BansRepository;
use App\Repository\TokensRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccountsController extends AbstractController{

    private $tokensRepository;
    private $userRepository;

    public function __construct(TokensRepository $tokensRepository, UserRepository $userRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/api/users", name="api.users", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        $users = $this->userRepository->findAll();

        return $this->json([
            'users' => $users
        ]);
    }

}