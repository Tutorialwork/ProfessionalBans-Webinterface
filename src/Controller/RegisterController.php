<?php

namespace App\Controller;

use App\Form\RegisterType;
use App\Repository\BansRepository;
use App\Repository\InviteRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/auth/register", name="register.")
 */
class RegisterController extends AbstractController
{
    private $userRepository;
    private $passwordEncoder;
    private $inviteRepository;
    private $bansRepository;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, InviteRepository $inviteRepository, BansRepository $bansRepository)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->inviteRepository = $inviteRepository;
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/{invite}", name="index")
     */
    public function index(Request $request, $invite)
    {
        $inviteObject = $this->inviteRepository->findOneBy(["code" => $invite]);
        if($inviteObject){
            $form = $this->createForm(RegisterType::class);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $user = $this->userRepository->findOneBy(
                    [
                        'username' => $form->get('username')->getData()
                    ]
                );

                if(!$user){
                    $player = $this->bansRepository->findOneBy(["Name" => $form->get('username')->getData()]);
                    if($player){
                        $newUser = $form->getData();
                        $newUser->setUUID($player->getUUID());
                        $newUser->setAuth(0);
                        $pwHash = $this->passwordEncoder->encodePassword($newUser, $newUser->getPassword());
                        $newUser->setPassword($pwHash);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($newUser);
                        $em->remove($inviteObject);
                        $em->flush();

                        return $this->redirectToRoute('auth.login');
                    } else {
                        $this->addFlash('error', 'Please join first the Minecraft server');
                    }
                } else {
                    $this->addFlash('error', 'This username is already taken');
                }
            }
        } else {
            $this->addFlash("error", "You can't access this page without invite code.");
            return $this->redirectToRoute("invite.index");
        }

        return $this->render('register/index.html.twig', [
            'register' => $form->createView()
        ]);
    }
}
