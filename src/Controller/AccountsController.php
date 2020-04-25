<?php

namespace App\Controller;

use App\Form\RoleType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        dump($_ENV);
        $_ENV["DATABASE_URL"] = "baums";
        dump($_ENV);

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
     * @Route("/manage", name="manage")
     */
    public function manage(){
        $form = $this->createForm(RoleType::class);

        return $this->render('accounts/manage.html.twig', [
            'role_manage' => $form->createView()
        ]);
    }
}
