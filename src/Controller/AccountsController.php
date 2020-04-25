<?php

namespace App\Controller;

use App\Form\RoleType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/accounts", name="accounts.")
 */
class AccountsController extends AbstractController
{

    private $userRepository;
    private $translator;

    public function __construct(UserRepository $userRepository, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="index")
     */
    public function list()
    {
        $users = $this->userRepository->findAll();

        return $this->render('accounts/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete($id){
        $account = $this->userRepository->findOneBy(["id" => $id]);

        if($account){
            $em = $this->getDoctrine()->getManager();
            $em->remove($account);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('account_deleted'));
        } else {
            $this->addFlash('error', $this->translator->trans('account_not_exits'));
        }

        return $this->redirectToRoute('accounts.index');
    }

    /**
     * @Route("/manage/{username}", name="manage")
     */
    public function manage($username, Request $request){
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if($user){
            $form = $this->createForm(RoleType::class);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $roles = $user->getRoles();

                if($form->get('bans')->getData()){
                    array_push($roles, "ROLE_PAGE_BANS");
                }
                if($form->get('mutes')->getData()){
                    array_push($roles, "ROLE_PAGE_MUTES");
                }
                if($form->get('reports')->getData()){
                    array_push($roles, "ROLE_PAGE_REPORTS");
                }
                if($form->get('unban_requests')->getData()){
                    array_push($roles, "ROLE_PAGE_UNBANS");
                }
                if($form->get('reasons')->getData()){
                    array_push($roles, "ROLE_PAGE_REASONS");
                }
                if($form->get('admin')->getData()){
                    array_push($roles, "ROLE_PAGE_ADMIN");
                }

                $user->setRoles($roles);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Permissions updated');
                return $this->redirectToRoute('accounts.index');
            }

            return $this->render('accounts/manage.html.twig', [
                'role_manage' => $form->createView(),
                'username' => $username
            ]);
        } else {
            $this->addFlash('error', $this->translator->trans('account_not_exits'));
            return $this->redirectToRoute('accounts.index');
        }
    }
}
