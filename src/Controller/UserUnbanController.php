<?php

namespace App\Controller;

use App\Form\CreateUnbanrequestType;
use App\Repository\BansRepository;
use App\Repository\UnbansRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/unbanrequest", name="userunban.")
 */
class UserUnbanController extends AbstractController
{

    private $translator;
    private $bansRepository;
    private $unbansRepository;

    public function __construct(TranslatorInterface $translator, BansRepository $bansRepository, UnbansRepository $unbansRepository)
    {
        $this->translator = $translator;
        $this->bansRepository = $bansRepository;
        $this->unbansRepository = $unbansRepository;
    }

    /**
     * @Route("/create", name="create")
     */
    public function index(Request $request)
    {
        $form = $this->createForm(CreateUnbanrequestType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $unban = $form->getData();
            $unban->setDate(time());
            $unban->setStatus(0);

            $ban = $this->bansRepository->findOneBy(['Name' => $unban->getUUID()]);
            $unban->setUUID($ban->getName());

            $em = $this->getDoctrine()->getManager();

            if($ban){
                if($ban->getBanned() == 1){
                    $unban->setUUID($ban->getUUID());
                    $unbans = $this->unbansRepository->findBy(['uuid' => $ban->getUUID()]);
                    if(count($unbans) == 0){
                        $em->persist($unban);
                        $em->flush();

                        $this->addFlash('success', $this->translator->trans('unbanrequest_success'));
                        return $this->redirectToRoute('userunban.create');
                    } else {
                        $diff = time() - 604800; //7 days limit
                        $lastUnban = end($unbans);
                        if($lastUnban->getDate() < $diff){
                            $em->persist($unban);
                            $em->flush();

                            $this->addFlash('success', $this->translator->trans('unbanrequest_success'));
                            return $this->redirectToRoute('userunban.create');
                        }

                        $this->addFlash('error', $this->translator->trans('already_requested'));
                        return $this->redirectToRoute('userunban.create');
                    }
                } else {
                    $this->addFlash('error', $this->translator->trans('player_not_banned'));
                }
            } else {
                $this->addFlash('error', $this->translator->trans('player_not_found'));
            }
        }

        return $this->render('user_unban/index.html.twig', [
            'unbanrequest' => $form->createView(),
        ]);
    }
}
