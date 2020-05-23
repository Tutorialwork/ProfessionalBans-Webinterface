<?php

namespace App\Controller\Api;

use App\Repository\BansRepository;
use App\Repository\ReasonRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PunishmentController extends AbstractController{

    private $tokensRepository;
    private $bansRepository;
    private $reasonRepository;

    public function __construct(TokensRepository $tokensRepository, BansRepository $bansRepository, ReasonRepository $reasonRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->bansRepository = $bansRepository;
        $this->reasonRepository = $reasonRepository;
    }

    /**
     * @Route("/api/punishments", name="api.punish", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        $bans = $this->bansRepository->findBy(['Banned' => 1]);
        $mutes = $this->bansRepository->findBy(['Muted' => 1]);

        foreach ($bans as $ban){
            $uuid = $this->bansRepository->findOneBy(['UUID' => $ban->getTeamUUID()]);
            $ban->setTeamUUID($uuid->getName());
        }

        foreach ($mutes as $mute){
            $uuid = $this->bansRepository->findOneBy(['UUID' => $mute->getTeamUUID()]);
            $mute->setTeamUUID($uuid->getName());
        }

        return $this->json([
            'bans' => $bans,
            'mutes' => $mutes
        ]);
    }

    /**
     * @Route("/api/punishments/ban", name="api.punish.ban", methods={"POST"})
     */
    public function ban(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }
        if(!array_key_exists("player", $request) || !array_key_exists("banid", $request)){
            return $this->json([
                'error' => 'Invalid request'
            ]);
        }

        $reason = $this->reasonRepository->findOneBy(['id' => $request->banid]);
        $player = $this->bansRepository->findOneBy(['Name' => $request->player]);
        if(!$player){
            return $this->json([
                'error' => 'Player not found'
            ], 404);
        }
        if(!$reason){
            return $this->json([
                'error' => 'Banreason not found'
            ], 404);
        }

        if(!$reason->getType()){
            $player->setBanned(true);
            $player->setMuted(false);
            $player->setBans($player->getBans() + 1);
        } else {
            $player->setBanned(false);
            $player->setMuted(true);
            $player->setMutes($player->getMutes() + 1);
        }
        $player->setReason($reason->getReason());
        $player->setEnd(time() * 1000 + $reason->getTime() * 60 * 1000);
        $player->setTeamUUID($token->getUuid());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->json([
            'success' => 'Player was banned'
        ]);
    }

    /**
     * @Route("/api/punishments/unban", name="api.punish.unban", methods={"POST"})
     */
    public function unban(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }
        if(!array_key_exists("player", $request)){
            return $this->json([
                'error' => 'Invalid request'
            ]);
        }

        $player = $this->bansRepository->findOneBy(['Name' => $request->player]);
        if(!$player){
            return $this->json([
                'error' => 'Player not found'
            ], 404);
        }

        $player->setBanned(false);
        $player->setMuted(false);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->json([
            'success' => 'Player was unbanned'
        ]);
    }

    /**
     * @Route("/api/punishments/reasons", name="api.punish.reasons", methods={"POST"})
     */
    public function reasons(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        $reasons = $this->reasonRepository->findAll();

        return $this->json([
            'reasons' => $reasons
        ]);
    }

    /**
     * @Route("/api/punishments/user", name="api.punish.user", methods={"POST"})
     */
    public function user(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }
        if(!array_key_exists("player", $request)){
            return $this->json([
                'error' => 'Invalid request'
            ], 400);
        }

        $player = $this->bansRepository->findOneBy(['Name' => $request->player]);
        if(!$player){
            return $this->json([
                'error' => 'Player not found'
            ], 404);
        }

        return $this->json([
            'player' => $player
        ]);
    }

}