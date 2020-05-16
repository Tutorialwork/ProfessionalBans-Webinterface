<?php

namespace App\Controller\Api;

use App\Repository\BansRepository;
use App\Repository\TokensRepository;
use App\Repository\UnbansRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UnbansController extends AbstractController{

    private $tokensRepository;
    private $unbansRepository;
    private $bansRepository;

    public function __construct(TokensRepository $tokensRepository, UnbansRepository $unbansRepository, BansRepository $bansRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->unbansRepository = $unbansRepository;
        $this->bansRepository = $bansRepository;
    }

    //TODO

    /**
     * @Route("/api/unbans", name="api.unbans", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        $openUnbans = $this->unbansRepository->findBy(['Status' => 0]);
        $closedUnbans = $this->unbansRepository->findBy(['Status' => [1, 2, 3]]);

        foreach ($openUnbans as $unban){
            $uuid = $this->bansRepository->findOneBy(['UUID' => $unban->getUUID()]);

            $unban->setUuid($uuid->getName());
        }

        foreach ($closedUnbans as $unban){
            $uuid = $this->bansRepository->findOneBy(['UUID' => $unban->getUUID()]);

            $unban->setUuid($uuid->getName());
        }

        return $this->json([
            'unbans' => [
                'open' => $openUnbans,
                'closed' => $closedUnbans
            ]
        ]);
    }

}