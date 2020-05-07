<?php

namespace App\Controller;

use App\Form\UnbanrequestType;
use App\Repository\BansRepository;
use App\Repository\UnbansRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/unbans", name="unban.")
 */
class UnbanController extends AbstractController
{

    private $unbansRepository;
    private $bansRepository;
    private $translator;

    public function __construct(UnbansRepository $unbansRepository, BansRepository $bansRepository, TranslatorInterface $translator)
    {
        $this->unbansRepository = $unbansRepository;
        $this->bansRepository = $bansRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $unbans = $this->unbansRepository->findBy(['Status' => 0]);

        foreach ($unbans as $unban){
            $playerUuid = $this->bansRepository->findOneBy(['UUID' => $unban->getUuid()]);

            $unban->setDate($unban->getDate());
            $unban->setUuid($playerUuid->getName());
        }

        return $this->render('unban/index.html.twig', [
            'unbans' => $unbans,
        ]);
    }

    /**
     * @Route("/archive", name="archive")
     */
    public function archive()
    {
        $unbans = $this->unbansRepository->findBy(['Status' => [1, 2, 3]]);

        foreach ($unbans as $unban){
            $playerUuid = $this->bansRepository->findOneBy(['UUID' => $unban->getUuid()]);

            $unban->setDate($unban->getDate());
            $unban->setUuid($playerUuid->getName());
        }

        return $this->render('unban/archive.html.twig', [
            'unbans' => $unbans,
        ]);
    }

    /**
     * @Route("/view/{id}", name="view")
     */
    public function view($id, Request $request){
        $unban = $this->unbansRepository->findOneBy(['id' => $id]);
        $playerUuid = $this->bansRepository->findOneBy(['UUID' => $unban->getUuid()]);

        $unban->setUuid($playerUuid->getName());
        $unban->setDate($unban->getDate());

        $oldEnd = $playerUuid->getEnd();
        $playerUuid->setEnd(round($playerUuid->getEnd()/1000));

        $form = $this->createForm(UnbanrequestType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $status = $form->get('status')->getData();
            $unban->setStatus($status);
            $unban->setUuid($playerUuid->getUUID());
            $em = $this->getDoctrine()->getManager();
            switch ($status){
                case 1:
                    $playerUuid->setBanned(0);

                    $em->flush();
                    break;
                case 2:
                    $newEnd = $form->get('endpicker')->getData();
                    $now = new \DateTime();

                    if($newEnd > $now){
                        $unban->setUuid($playerUuid->getUUID());
                        $playerUuid->setEnd(round($newEnd->getTimestamp() * 1000));

                        $em->flush();

                        return $this->redirectToRoute('unban.index');
                    } else {
                        $this->addFlash('error', $this->translator->trans('date_past'));
                    }
                    break;
                case 3:
                    $playerUuid->setEnd($oldEnd);
                    $em->flush();
                    break;
            }
            return $this->redirectToRoute('unban.index');
        }

        return $this->render('unban/view.html.twig', [
            'unban' => $unban,
            'form' => $form->createView(),
            'player' => $playerUuid
        ]);
    }

    /**
     * @Route("/request", name="request")
     */
    public function requestUnban()
    {
        return $this->render('unban/index.html.twig', [
            'controller_name' => 'UnbanController',
        ]);
    }
}
