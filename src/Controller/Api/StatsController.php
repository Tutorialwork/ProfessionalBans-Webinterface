<?php

namespace App\Controller\Api;

use App\Repository\BansRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController{

    private $tokensRepository;
    private $bansRepository;

    public function __construct(TokensRepository $tokensRepository, BansRepository $bansRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/api/stats", name="api.stats", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        $players = $this->bansRepository->findAll();
        $bans = $this->bansRepository->findBy(['Banned' => 1]);
        $mutes = $this->bansRepository->findBy(['Muted' => 1]);

        return $this->json([
            'players' => count($players),
            'bans' => count($bans),
            'mutes' => count($mutes)
        ]);
    }

}