<?php

namespace App\Controller\Api;

use App\Repository\BansRepository;
use App\Repository\LogRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController{

    private $tokensRepository;
    private $bansRepository;
    private $logRepository;

    public function __construct(TokensRepository $tokensRepository, BansRepository $bansRepository, LogRepository $logRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->bansRepository = $bansRepository;
        $this->logRepository = $logRepository;
    }

    /**
     * @Route("/api/log", name="api.log", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }
        if(array_key_exists("player", $request)){
            $userUuid = $this->bansRepository->findOneBy(['Name' => $request->player]);
            if(!$userUuid){
                return $this->json([
                    'error' => 'User not found'
                ], 404);
            }
            $logs = $this->logRepository->findBy(['UUID' => $userUuid->getUUID()], ['Date' => 'DESC']);

            foreach ($logs as $log){
                $uuid = $this->bansRepository->findOneBy(['UUID' => $log->getUUID()]);
                $teamUuid = $this->bansRepository->findOneBy(['UUID' => ($log->getByUUID() != null) ? $log->getByUUID() : '']);
                $log->setUUID($uuid->getName());
                $log->setByUUID(($teamUuid) ? $teamUuid->getName() : $log->getByUUID());
            }

            return $this->json([
                'log' => $logs
            ]);
        } else {
            $logs = $this->logRepository->findBy([], ['Date' => 'DESC']);

            foreach ($logs as $log){
                $uuid = $this->bansRepository->findOneBy(['UUID' => $log->getUUID()]);
                $teamUuid = $this->bansRepository->findOneBy(['UUID' => ($log->getByUUID() != null) ? $log->getByUUID() : '']);
                $log->setUUID($uuid->getName());
                $log->setByUUID(($teamUuid) ? $teamUuid->getName() : $log->getByUUID());
            }

            return $this->json([
                'log' => $logs
            ]);
        }

        $token->setFirebaseToken($request->firebaseToken);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->json([
            'success' => 'The firebasetoken was successfully for '.$token->getUuid().' updated',
        ]);
    }

}