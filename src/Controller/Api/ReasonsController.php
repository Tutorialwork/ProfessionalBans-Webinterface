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

class ReasonsController extends AbstractController{

    private $tokensRepository;
    private $reasonRepository;

    public function __construct(TokensRepository $tokensRepository, ReasonRepository $reasonRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->reasonRepository = $reasonRepository;
    }

    /**
     * @Route("/api/reasons/delete", name="api.reasons.delete", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        if(array_key_exists("reason", $request)){
            $reason = $this->reasonRepository->findOneBy(['id' => $request->reason]);
            if(!$reason){
                return $this->json([
                    'error' => 'Reason not found',
                ], 404);
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($reason);
            $em->flush();

            return $this->json([
                'success' => 'Reason deleted',
            ]);
        } else {
            return $this->json([
                'error' => 'Invalid request',
            ], 400);
        }
    }

}