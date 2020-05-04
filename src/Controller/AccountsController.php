<?php

namespace App\Controller;

use App\Form\EditAccountType;
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
     * @Route("/edit/{id}", name="edit")
     */
    public function edit($id, Request $request){
        $account = $this->userRepository->findOneBy(["id" => $id]);

        if($account){
            $form = $this->createForm(EditAccountType::class, $account);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $account = $form->getData();
            }

            return $this->render('accounts/edit.html.twig', [
                'form' => $form->createView()
            ]);
        } else {
            $this->addFlash('error', $this->translator->trans('account_not_exits'));
            return $this->redirectToRoute('accounts.index');
        }
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
            $roles = $user->getRoles();
            foreach ($roles as $role){
                if($role == "ROLE_SUPER_ADMIN"){
                    /*
                     * NOT PERMITTED
                     * CANCEL!
                     */
                    $this->addFlash('error', $this->translator->trans('not_permitted'));
                    return $this->redirectToRoute('accounts.index');
                }
            }

            $formArray = array();
            foreach ($roles as $role){
                $formArray[$role] = true;
            }

            $form = $this->createForm(RoleType::class, $formArray);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $roles = $user->getRoles();

                if($form->get('ROLE_PAGE_BAN')->getData()){
                    if(!$this->duplicatChecker($roles, "ROLE_PAGE_BAN")){
                        array_push($roles, "ROLE_PAGE_BAN");
                    }
                }
                if($form->get('ROLE_PAGE_MUTE')->getData()){
                    if(!$this->duplicatChecker($roles, "ROLE_PAGE_MUTE")){
                        array_push($roles, "ROLE_PAGE_MUTE");
                    }
                }
                if($form->get('ROLE_PAGE_REPORTS')->getData()){
                    if(!$this->duplicatChecker($roles, "ROLE_PAGE_REPORTS")){
                        array_push($roles, "ROLE_PAGE_REPORTS");
                    }
                }
                if($form->get('ROLE_PAGE_UNBANS')->getData()){
                    if(!$this->duplicatChecker($roles, "ROLE_PAGE_UNBANS")){
                        array_push($roles, "ROLE_PAGE_UNBANS");
                    }
                }
                if($form->get('ROLE_PAGE_REASON')->getData()){
                    if(!$this->duplicatChecker($roles, "ROLE_PAGE_REASON")){
                        array_push($roles, "ROLE_PAGE_REASON");
                    }
                }
                if($form->get('ROLE_PAGE_ADMIN')->getData()){
                    if(!$this->duplicatChecker($roles, "ROLE_PAGE_ADMIN")){
                        array_push($roles, "ROLE_PAGE_ADMIN");
                    }
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

    private function duplicatChecker($roles, $role_name){
        $repsonse_array = array_keys($roles, $role_name);
        if(count($repsonse_array) == 0){
            return false;
        } else {
            return true;
        }
    }
}
