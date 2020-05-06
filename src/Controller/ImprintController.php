<?php

namespace App\Controller;

use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/utils", name="utils.")
 */
class ImprintController extends AbstractController
{
    private $settingRepository;
    private $translator;

    public function __construct(SettingRepository $settingRepository, TranslatorInterface $translator)
    {
        $this->settingRepository = $settingRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/imprint", name="imprint")
     */
    public function imprint()
    {
        $settingImprint = $this->settingRepository->findOneBy(['Name' => 'imprint']);
        if($settingImprint && $settingImprint->getValue()){
            return $this->redirect($settingImprint->getValue());
        } else {
            $this->addFlash('error', $this->translator->trans('page_not_found'));
            return $this->redirectToRoute('chatlogs.find');
        }
    }

    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacy()
    {
        $settingPrivacy = $this->settingRepository->findOneBy(['Name' => 'privacy']);
        if($settingPrivacy && $settingPrivacy->getValue()){
            return $this->redirect($settingPrivacy->getValue());
        } else {
            $this->addFlash('error', $this->translator->trans('page_not_found'));
            return $this->redirectToRoute('chatlogs.find');
        }
    }
}
