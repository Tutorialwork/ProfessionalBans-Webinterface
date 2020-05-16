<?php

namespace App\Controller\Api;

use App\Repository\BansRepository;
use App\Repository\ChatlogRepository;
use App\Repository\ReportsRepository;
use App\Repository\TokensRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReportsController extends AbstractController{

    private $tokensRepository;
    private $reportsRepository;
    private $chatlogRepository;
    private $bansRepository;

    public function __construct(TokensRepository $tokensRepository, ReportsRepository $reportsRepository, ChatlogRepository $chatlogRepository, BansRepository $bansRepository)
    {
        $this->tokensRepository = $tokensRepository;
        $this->reportsRepository = $reportsRepository;
        $this->chatlogRepository = $chatlogRepository;
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/api/reports", name="api.reports", methods={"POST"})
     */
    public function index(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }

        $openReports = $this->reportsRepository->findBy(['Status' => 0], ['CreatedAt' => 'DESC']);
        $closedReports = $this->reportsRepository->findBy(['Status' => 1], ['CreatedAt' => 'DESC']);
        $chatlogs = $this->chatlogRepository->findBy([], ['CreatedAt' => 'DESC']);

        foreach ($openReports as $report){
            $uuid = $this->bansRepository->findOneBy(['UUID' => $report->getUuid()]);
            $reporterUuid = $this->bansRepository->findOneBy(['UUID' => $report->getReporter()]);

            $report->setUuid($uuid->getName());
            $report->setReporter($reporterUuid->getName());
        }

        foreach ($closedReports as $report){
            $uuid = $this->bansRepository->findOneBy(['UUID' => $report->getUuid()]);
            $reporterUuid = $this->bansRepository->findOneBy(['UUID' => $report->getReporter()]);
            $teamUuid = $this->bansRepository->findOneBy(['UUID' => $report->getTeam()]);

            $report->setUuid($uuid->getName());
            $report->setReporter($reporterUuid->getName());
            $report->setTeam($teamUuid->getName());
        }

        foreach ($chatlogs as $chatlog){
            $uuid = $this->bansRepository->findOneBy(['UUID' => $chatlog->getUuid()]);
            $creatorUuid = $this->bansRepository->findOneBy(['UUID' => $chatlog->getCreatorUuid()]);

            $chatlog->setUuid($uuid->getName());
            $chatlog->setCreatorUuid($creatorUuid->getName());
        }

        return $this->json([
            'reports' => [
                'open' => $openReports,
                'closed' => $closedReports
            ],
            'chatlogs' => $chatlogs
        ]);
    }

    /**
     * @Route("/api/reports/finish", name="api.reports.finish", methods={"POST"})
     */
    public function finish(Request $request){
        $request = json_decode($request->getContent());
        $token = $this->tokensRepository->findOneBy(['token' => (array_key_exists("token", $request)) ? $request->token : null]);
        if(!$token){
            return $this->json([
                'error' => 'Not permitted'
            ], 401);
        }
        if(!array_key_exists("reportId", $request)){
            return $this->json([
                'error' => 'Invalid request'
            ], 400);
        }

        $report = $this->reportsRepository->findOneBy(['id' => $request->reportId]);
        if(!$report){
            return $this->json([
                'error' => 'Report not found'
            ], 404);
        }

        $report->setStatus(1);
        $report->setTeam($token->getUuid());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->json([
            'success' => 'Finished report #'.$request->reportId
        ]);
    }

}