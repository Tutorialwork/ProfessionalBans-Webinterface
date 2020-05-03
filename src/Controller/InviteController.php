<?php

namespace App\Controller;

use App\Entity\Invite;
use App\Form\InviteType;
use App\Repository\InviteRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class InviteController extends AbstractController
{
    private $inviteRepository;
    private $userRepository;
    private $translator;

    public function __construct(InviteRepository $inviteRepository, UserRepository $userRepository, TranslatorInterface $translator)
    {
        $this->inviteRepository = $inviteRepository;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/auth/register", name="invite.index")
     */
    public function index(Request $request)
    {
        $form = $this->createForm(InviteType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $invite = $this->inviteRepository->findOneBy(
                [
                    'code' => $form->get('code')->getData()
                ]
            );

            if($invite){
                return $this->redirectToRoute('register.index', ['invite' => $form->get('code')->getData()]);
            } else {
                $this->addFlash('error', $this->translator->trans('invite_invalid'));
            }
        }

        return $this->render('invite/index.html.twig', [
            'invite' => $form->createView()
        ]);
    }

    /**
     * @Route("/invites", name="admin.invites")
     */
    public function list(){
        $invites = $this->inviteRepository->findAll();
        $users = $this->userRepository->findAll();

        $usernames = [];
        foreach ($users as $user){
            $usernames[$user->getId()] = $user->getUsername();
        }

        return $this->render('invite/list.html.twig', [
            'invites' => $invites,
            'usernames' => $usernames
        ]);
    }

    /**
     * @Route("/invite/delete/{id}", name="admin.invite.delete")
     */
    public function delete($id){
        $invite = $this->inviteRepository->findOneBy(["id" => $id]);

        if($invite){
            $em = $this->getDoctrine()->getManager();
            $em->remove($invite);
            $em->flush();

            $this->addFlash("success", $this->translator->trans('invite_delete'));
        } else {
            $this->addFlash("error", "Entry not found");
        }

        return $this->redirectToRoute("admin.invites");
    }

    /**
     * @Route("/invite/add", name="admin.invite.add")
     */
    public function create(){
        $invite = new Invite();
        $invite->setCode(bin2hex(random_bytes(16)));
        $invite->setCreator($this->getUser()->getId());
        $invite->setCreationdate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($invite);
        $em->flush();

        return $this->redirectToRoute("admin.invites");
    }
}
