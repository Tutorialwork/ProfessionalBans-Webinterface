<?php

namespace App\Controller;

use App\Entity\Reasons;
use App\Form\EditReasonType;
use App\Form\ReasonType;
use App\Repository\ReasonRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/reasons", name="reason.")
 */
class ReasonController extends AbstractController
{

    private $reasonRepository;
    private $paginator;
    private $translator;

    public function __construct(ReasonRepository $reasonRepository, PaginatorInterface $paginator, TranslatorInterface $translator)
    {
        $this->reasonRepository = $reasonRepository;
        $this->paginator = $paginator;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $reasons = $this->reasonRepository->findAll();

        $page_reasons = $this->paginator->paginate(
            $reasons,
            $request->query->getInt('page', 1), /*page number*/
            10
        );

        return $this->render('reason/index.html.twig', [
            'reasons' => $page_reasons
        ]);
    }

    /**
     * @Route("/add", name="add")
     */
    public function add(Request $request){
        $form = $this->createForm(ReasonType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $reason = new Reasons();

            $reason->setReason($form->get('Reason')->getData());

            $time = (int) $form->get('Time')->getData();
            $type = (int) $form->get('UnitType')->getData();

            if($time == 0 && $type != 3){
                $this->addFlash('error', $this->translator->trans('err_time'));
            } else {
                switch ($type){
                    case 1:
                        //Hours
                        $time = $time * 60;
                        break;
                    case 2:
                        //Days
                        $time = $time * 24 * 60;
                        break;
                    case 3:
                        //Permanent
                        $time = -1;
                        break;
                }

                $reason->setTime($time);

                $reason->setType($form->get('Type')->getData());
                $reason->setPerms($form->get('Perms')->getData());


                $reason->setAddedAt(new \DateTime());
                $reason->setBans(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($reason);
                $em->flush();

                $this->addFlash('success', $this->translator->trans('success_added'));
                return $this->redirectToRoute('reason.index');
            }
        }

        return $this->render('reason/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete($id){
        $reason = $this->reasonRepository->findOneBy(['id' => $id]);

        $em = $this->getDoctrine()->getManager();

        if($reason){
            $em->remove($reason);
            $em->flush();

            $this->addFlash('success', 'Reason #'.$id.' was successfully deleted.');
        } else {
            $this->addFlash('error', 'Reason not found');
        }

        return $this->redirectToRoute('reason.index');
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit($id){
        $reason = $this->reasonRepository->findOneBy(['id' => $id]);

        $form = $this->createForm(EditReasonType::class);

        $em = $this->getDoctrine()->getManager();

        if(!$reason){
            $this->addFlash('error', 'Reason not found');
            return $this->redirectToRoute('reason.index');
        }

        return $this->render('reason/edit.html.twig', [
            'reason' => $reason,
            'form' => $form->createView()
        ]);
    }
}
