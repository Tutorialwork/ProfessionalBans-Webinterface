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

        if(!array_key_exists("unban", $request) || !array_key_exists("status", $request)){
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

        if($request->status == "2"){
            if(!array_key_exists("date", $request)){
                return $this->json([
                    'error' => 'Invalid request'
                ], 400);
            }
        }

        $unban = $this->unbansRepository->findOneBy(['id' => $request->unban]);
        if($unban){
            $unban->setStatus((int)$request->status);
            $ban = $this->bansRepository->findOneBy(['UUID' => $unban->getUuid()]);

            switch ($request->status){
                case "1":
                    $ban->setBanned(0);
                    break;
                case "2":
                    $date = new \DateTime($request->date);
                    $timestamp = $date->getTimestamp();
                    $timestamp = $timestamp * 1000;
                    $ban->setEnd($timestamp);
                    break;
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->json([
                'success' => 'Unban request was updated'
            ]);
        } else {
            return $this->json([
                'error' => 'Unban request not found'
            ], 404);
        }
    }

}