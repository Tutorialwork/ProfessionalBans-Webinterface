<?php

namespace App\Controller;

use App\Form\FindChatlogType;
use App\Repository\BansRepository;
use App\Repository\ChatlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ViewChatlogController extends AbstractController
{

    private $chatlogRepository;
    private $bansRepository;
    private $translator;

    public function __construct(ChatlogRepository $chatlogRepository, BansRepository $bansRepository, TranslatorInterface $translator)
    {
        $this->chatlogRepository = $chatlogRepository;
        $this->bansRepository = $bansRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/chatlogs/{id}", name="chatlogs.view")
     */
    public function index($id)
    {
        $chatlogs = $this->chatlogRepository->findBy(['Logid' => $id], ['Senddate' => 'DESC']);

        if($chatlogs != null){
            foreach ($chatlogs as $chatlog){
                $senderUuid = $this->bansRepository->findOneBy(['UUID' => $chatlog->getUuid()]);

                $chatlog->setSenddate(round($chatlog->getSenddate()/1000));
                $chatlog->setUuid($senderUuid->getName());
            }

            return $this->render('view_chatlog/index.html.twig', [
                'chatlogs' => $chatlogs,
                'chatlog' => $chatlogs[0]
            ]);
        } else {
            return $this->render('view_chatlog/index.html.twig', [
                'chatlog' => null,
            ]);
        }
    }

    /**
     * @Route("/find/chatlog", name="chatlogs.find")
     */
    public function find(Request $request){
        $form = $this->createForm(FindChatlogType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $chatlog = $form->getData();
            $chatlog_tester = $this->chatlogRepository->findOneBy(['Logid' => $chatlog->getLogId()]);

            if($chatlog_tester){
                return $this->redirectToRoute('chatlogs.view', ['id' => $chatlog_tester->getLogid()]);
            } else {
                $this->addFlash('error', $this->translator->trans('chatlog_not_found'));
            }
        }

        return $this->render('view_chatlog/find.html.twig', [
            'find' => $form->createView()
        ]);
    }
}
