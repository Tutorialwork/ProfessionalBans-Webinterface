<?php

namespace App\Controller;

use App\Repository\BansRepository;
use App\Repository\ReportsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/reports", name="reports.")
 */
class ReportsController extends AbstractController
{

    private $reportsRepository;
    private $bansRepository;
    private $translator;

    public function __construct(ReportsRepository $reportsRepository, BansRepository $bansRepository, TranslatorInterface $translator)
    {
        $this->reportsRepository = $reportsRepository;
        $this->bansRepository = $bansRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $open_reportes = $this->reportsRepository->findBy(['Status' => 0], ['CreatedAt' => 'DESC']);

        foreach ($open_reportes as $reports) {
            $reported = $this->bansRepository->findOneBy(['UUID' => $reports->getUuid()]);
            $reporter = $this->bansRepository->findOneBy(['UUID' => $reports->getReporter()]);

            $reports->setReporter($reporter->getName());
            $reports->setUuid($reported->getName());
            $reports->setCreatedAt(round($reports->getCreatedAt()/1000));
        }

        return $this->render('reports/index.html.twig', [
            'reports' => $open_reportes,
        ]);
    }

    /**
     * @Route("/archive", name="archive")
     */
    public function archive()
    {
        $closed_reports = $this->reportsRepository->findBy(['Status' => 1], ['CreatedAt' => 'DESC']);

        foreach ($closed_reports as $reports) {
            $reported = $this->bansRepository->findOneBy(['UUID' => $reports->getUuid()]);
            $reporter = $this->bansRepository->findOneBy(['UUID' => $reports->getReporter()]);
            $teamUuid = $this->bansRepository->findOneBy(['UUID' => $reports->getTeam()]);

            $reports->setReporter($reporter->getName());
            $reports->setUuid($reported->getName());
            $reports->setCreatedAt(round($reports->getCreatedAt()/1000));
            $reports->setTeam($teamUuid->getName());
        }

        return $this->render('reports/archive.html.twig', [
            'reports' => $closed_reports,
        ]);
    }

    /**
     * @Route("/{id}", name="delete")
     */
    public function done($id)
    {
        $report = $this->reportsRepository->findOneBy(['id' => $id]);
        if($report){
            $report->setStatus(true);
            $teamUuid = $this->bansRepository->findOneBy(['Name' => $this->getUser()->getUsername()]);
            $report->setTeam($teamUuid->getUUID());

            $em = $this->getDoctrine()->getManager();
            $em->persist($report);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('report_done_success'));

            return $this->redirectToRoute('reports.index');
        } else {
            $this->addFlash('error', 'This report was not found');
        }
    }
}
