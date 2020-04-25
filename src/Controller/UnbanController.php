<?php

namespace App\Controller;

use App\Form\UnbanrequestType;
use App\Repository\BansRepository;
use App\Repository\UnbansRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/unbans", name="unban.")
 */
class UnbanController extends AbstractController
{

    private $unbansRepository;
    private $bansRepository;

    public function __construct(UnbansRepository $unbansRepository, BansRepository $bansRepository)
    {
        $this->unbansRepository = $unbansRepository;
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $unbans = $this->unbansRepository->findAll();

        foreach ($unbans as $unban){
            $playerUuid = $this->bansRepository->findOneBy(['UUID' => $unban->getUuid()]);

            $unban->setDate(round($unban->getDate()/1000));
            $unban->setUuid($playerUuid->getName());
        }

        return $this->render('unban/index.html.twig', [
            'unbans' => $unbans,
        ]);
    }

    /**
     * @Route("/view/{id}", name="view")
     */
    public function view($id, Request $request){
        $form = $this->createForm(UnbanrequestType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            dump($form->getData());
        }

        $unban = $this->unbansRepository->findOneBy(['id' => $id]);
        $playerUuid = $this->bansRepository->findOneBy(['UUID' => $unban->getUuid()]);

        $unban->setUuid($playerUuid->getName());
        $unban->setDate(round($unban->getDate()/1000));

        $playerUuid->setEnd(round($playerUuid->getEnd()/1000));

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
