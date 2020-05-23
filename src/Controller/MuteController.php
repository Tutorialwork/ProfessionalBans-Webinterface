<?php

namespace App\Controller;

use App\Form\BanType;
use App\Form\MuteType;
use App\Repository\BansRepository;
use App\Repository\ReasonRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/mutes", name="mute.")
 */
class MuteController extends AbstractController
{

    private $bansRepository;
    private $reasonRepository;
    private $userRepository;
    private $paginator;
    private $translator;

    public function __construct(BansRepository $bansRepository, ReasonRepository $reasonRepository, UserRepository $userRepository, PaginatorInterface $paginator, TranslatorInterface $translator)
    {
        $this->bansRepository = $bansRepository;
        $this->reasonRepository = $reasonRepository;
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $mutes = $this->bansRepository->findBy(["Muted" => 1]);

        foreach ($mutes as $mute){
            $punisherMute = $this->bansRepository->findOneBy(["UUID" => $mute->getTeamUUID()]);
            $mute->setTeamUUID($punisherMute->getName());
        }

        $mutes_page = $this->paginator->paginate(
            $mutes,
            $request->query->getInt('page', 1), /*page number*/
            10
        );

        return $this->render('mute/index.html.twig', [
            'mutes' => $mutes_page
        ]);
    }

    /**
     * @Route("/mute", name="mute")
     */
    public function mute(Request $request){
        $form = $this->createForm(MuteType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $ban = $this->bansRepository->findOneBy(["Name" => $form->get('Name')->getData()]);
            $authedUser = $this->userRepository->findOneBy(["username" => $this->getUser()->getUsername()]);
            $reason = $form->get('Reason')->getData();

            if($ban){
                if($reason){
                    if($ban->getBanned() == 0){
                        /*
                        * Calc when ban ends
                        */
                        if($reason->getTime() != -1){
                            $banTime = $reason->getTime() * 60 * 1000;
                            $now = time() * 1000;
                            $endTime = $now + $banTime;
                        } else {
                            $endTime = -1;
                        }

                        $ban->setMuted(1);
                        $ban->setReason($reason->getReason());
                        $ban->setEnd(intval($endTime));
                        $ban->setTeamUUID($authedUser->getUuid());

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($ban);
                        $em->flush();

                        return $this->redirectToRoute("mute.index");
                    } else {
                        $this->addFlash("error", $this->translator->trans('already_muted'));
                    }
                } else {
                    $this->addFlash('error', $this->translator->trans('select_reason'));
                }
            } else {
                $this->addFlash("error", $this->translator->trans('player_not_found'));
            }
        }

        return $this->render('mute/mute.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/unmute/{name}", name="unmute")
     */
    public function unmute($name){
        $mute = $this->bansRepository->findOneBy(
            [
                "Name" => $name
            ]
        );

        if($mute){
            $mute->setMuted(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($mute);
            $em->flush();

            $this->addFlash("success", $name." was successfully unmuted");
        } else {
            $this->addFlash("error", "This player could not be found");
        }

        return $this->redirectToRoute("mute.index");
    }

}
