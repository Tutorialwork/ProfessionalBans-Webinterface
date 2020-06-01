<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Form\SettingsType;
use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/settings", name="settings.")
 */
class SettingsController extends AbstractController
{

    private $translator;
    private $settingRepository;

    public function __construct(TranslatorInterface $translator, SettingRepository $settingRepository)
    {
        $this->translator = $translator;
        $this->settingRepository = $settingRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $settingImprint = $this->settingRepository->findOneBy(['Name' => 'imprint']);
        $settingPrivacy = $this->settingRepository->findOneBy(['Name' => 'privacy']);
        $em = $this->getDoctrine()->getManager();

        if(!$settingImprint && !$settingPrivacy){
            $imprint = new Setting();
            $imprint->setName("imprint");

            $privacy = new Setting();
            $privacy->setName("privacy");

            $em->persist($imprint);
            $em->persist($privacy);
            $em->flush();
        }

        $form = $this->createForm(SettingsType::class, ['imprint' => $settingImprint->getValue(), 'privacy' => $settingPrivacy->getValue()]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $imprint = $form->get('imprint')->getData();
            $privacy = $form->get('privacy')->getData();

            if($imprint){
                if(filter_var($imprint, FILTER_VALIDATE_URL)){
                    $imprint = filter_var($imprint, FILTER_SANITIZE_URL);
                    $this->addFlash('success', $this->translator->trans('changes_saved'));
                } else {
                    $imprint = null;
                    $this->addFlash('error', $this->translator->trans('no_url'));
                }
            }

            if($privacy){
                if(filter_var($privacy, FILTER_VALIDATE_URL)){
                    $privacy = filter_var($privacy, FILTER_SANITIZE_URL);
                    $this->addFlash('success', $this->translator->trans('changes_saved'));
                } else {
                    $privacy = null;
                    $this->addFlash('error', $this->translator->trans('no_url'));
                }
            }

            $settingImprint->setValue($imprint);
            $settingPrivacy->setValue($privacy);

            $em->persist($settingImprint);
            $em->persist($settingPrivacy);
            $em->flush();
        }

        return $this->render('settings/index.html.twig', [
            'settings' => $form->createView(),
        ]);
    }
}
