<?php

namespace App\Controller\Api;

use App\Entity\Privatemessages;
use App\Repository\BansRepository;
use App\Repository\PrivatemessageRepository;
use App\Repository\ReasonRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController{

    private $tokensRepository;
    private $bansRepository;

    public function __construct(TokensRepository $tokensRepository, BansRepository $bansRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/api/search/{query}", name="api.search", methods={"POST"})
     */
    public function index(Request $request, $query){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        $search = $this->bansRepository->search($query);
        if(count($search) != 0){
            return $this->json([
                'results' => $search,
            ]);
        } else {
            return $this->json([
                'error' => 'No results found',
            ], 404);
        }
    }

}