<?php

namespace App\Controller;

use App\Form\BanType;
use App\Repository\BansRepository;
use App\Repository\ReasonRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/bans", name="ban.")
 */
class BanController extends AbstractController
{

    private $bansRepository;
    private $reasonRepository;
    private $userRepository;
    private $paginator;

    public function __construct(BansRepository $bansRepository, ReasonRepository $reasonRepository, UserRepository $userRepository, PaginatorInterface $paginator)
    {
        $this->bansRepository = $bansRepository;
        $this->reasonRepository = $reasonRepository;
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $bans = $this->bansRepository->findBy(["Banned" => 1]);

        foreach ($bans as $ban){
            $punisherBan = $this->bansRepository->findOneBy(["Teamuuid" => $ban->getTeamUUID()]);
            $ban->setTeamUUID($punisherBan->getName());
        }

        $bans_page = $this->paginator->paginate(
            $bans,
            $request->query->getInt('page', 1), /*page number*/
            10
        );

        return $this->render('ban/index.html.twig', [
            'bans' => $bans_page
        ]);
    }

    /**
     * @Route("/ban", name="ban")
     */
    public function ban(Request $request){
        $form = $this->createForm(BanType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $ban = $this->bansRepository->findOneBy(["Name" => $form->get('Name')->getData()]);
            $authedUser = $this->userRepository->findOneBy(["username" => $this->getUser()->getUsername()]);
            $reason = $form->get('Reason')->getData();

            if($ban){
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

                    $ban->setBanned(1);
                    $ban->setReason($reason->getReason());
                    $ban->setEnd($endTime);
                    $ban->setTeamUUID($authedUser->getUuid());

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($ban);
                    $em->flush();

                    return $this->redirectToRoute("ban.index");
                } else {
                    $this->addFlash("error", "This player is already banned");
                }
            } else {
                $this->addFlash("error", "This player could not found");
            }
        }

        return $this->render('ban/ban.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/unban/{name}", name="unban")
     */
    public function unban($name){
        $ban = $this->bansRepository->findOneBy(
            [
                "Name" => $name
            ]
        );

        if($ban){
            $ban->setBanned(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($ban);
            $em->flush();

            $this->addFlash("success", $name." was successfully unbanned");
        } else {
            $this->addFlash("error", "This player could not be found");
        }

        return $this->redirectToRoute("ban.index");
    }
}
