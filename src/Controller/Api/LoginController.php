<?php

namespace App\Controller\Api;

use App\Entity\Tokens;
use App\Repository\TokensRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoginController extends AbstractController{

    private $tokensRepository;
    private $userRepository;
    private $passwordEncoder;

    public function __construct(TokensRepository $tokensRepository, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->tokensRepository = $tokensRepository;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/api/login", name="api.login", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        if(!array_key_exists("username", $request) || !array_key_exists("password", $request)){
            return $this->json([
                'error' => 'Invalid request'
            ], 400);
        }

        $token = new Tokens();

        $user = $this->userRepository->findOneBy(['username' => $request->username]);
        if(!$user){
            return $this->json([
                'error' => 'Login failed'
            ], 401);
        }
        if(!$this->passwordEncoder->isPasswordValid($user, $request->password)){
            return $this->json([
                'error' => 'Login failed'
            ], 401);
        }

        $tokenString = bin2hex(random_bytes(16));

        $token->setUuid($user->getUuid());
        $token->setToken($tokenString);
        $token->setTokenDescription((array_key_exists("description", $request)) ? $request->description : null);
        $token->setCreatedAt(new \DateTime());
        $token->setUpdatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($token);
        $em->flush();

        return $this->json([
            'token' => $tokenString
        ]);
    }

}