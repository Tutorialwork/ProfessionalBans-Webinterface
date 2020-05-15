<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Repository\LogRepository;
use App\Repository\ReasonRepository;
use DateTime;
use App\Repository\BansRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/user", name="profile.")
 */
class ProfileController extends AbstractController
{

    private $bansRepository;
    private $userRepository;
    private $logRepository;
    private $reasonRepository;
    private $homeController;
    private $passwordEncoder;
    private $paginator;
    private $translator;
    private $googleAuthenticator;

    public function __construct(BansRepository $bansRepository, UserRepository $userRepository, LogRepository $logRepository, ReasonRepository $reasonRepository, HomeController $homeController, UserPasswordEncoderInterface $passwordEncoder, PaginatorInterface $paginator, TranslatorInterface $translator, GoogleAuthenticatorInterface $googleAuthenticator)
    {
        $this->bansRepository = $bansRepository;
        $this->userRepository = $userRepository;
        $this->logRepository = $logRepository;
        $this->reasonRepository = $reasonRepository;
        $this->homeController = $homeController;
        $this->passwordEncoder = $passwordEncoder;
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->googleAuthenticator = $googleAuthenticator;
    }

    /**
     * @Route("/{user}", name="user")
     */
    public function showProfile($user, Request $request)
    {
        $player = $this->bansRepository->findOneBy(["Name" => $user]);
        if(!$player){
            $this->addFlash('error', $this->translator->trans('player_not_found'));
            return $this->redirectToRoute('home.index');
        }
        $user = $this->userRepository->findOneBy(["username" => $user]);
        $log = $this->logRepository->findBy(["Action" => ['BAN', 'UNBAN_BAN', 'MUTE', 'UNBAN_MUTE', 'AUTOMUTE_BLACKLIST', 'AUTOMUTE_ADBLACKLIST'], 'UUID' => $player->getUUID()], ['Date' => "DESC"], 10);

        $onlinetime = null;
        if($player != null){
            $player->setLastlogin(round($player->getLastlogin()/1000));
            $player->setFirstlogin(round($player->getFirstlogin()/1000));

            $onlinetime = $this->getFormattedTime($player->getOnlinetime());
        }

        foreach ($log as $log_entry){
            $uuidPlayer = $this->bansRepository->findOneBy(["UUID" => $log_entry->getUUID()]);
            $teamuuidPlayer = $this->bansRepository->findOneBy(["UUID" => ($log_entry->getByUUID() != null) ? $log_entry->getByUUID() : 'random']);

            $integerCheck = (int) $log_entry->getNote();
            if($integerCheck != 0){
                $reason = $this->reasonRepository->findOneBy(["id" => $integerCheck]);
                $log_entry->setNote($reason->getReason());
            }

            $log_entry->setUUID($uuidPlayer->getName());
            $log_entry->setByUUID(($teamuuidPlayer != null) ? $teamuuidPlayer->getName() : '');
            $log_entry->setAction(str_replace("%text%", $log_entry->getNote(), $this->homeController->resolveAction($log_entry)));
            $log_entry->setDate(round($log_entry->getDate() / 1000));
        }

        $passwordChangeForm = $this->createForm(ChangePasswordType::class);
        $passwordChangeForm->handleRequest($request);

        if($passwordChangeForm->isSubmitted() && $passwordChangeForm->isValid()){
            $user = $passwordChangeForm->getData();
            $hash = $this->passwordEncoder->encodePassword($this->getUser(), $user->getPassword());
            $this->getUser()->setPassword($hash);

            $em = $this->getDoctrine()->getManager();
            $em->persist($this->getUser());
            $em->flush();

            $this->addFlash('success', $this->translator->trans('pw_changed'));
        }

        $logs_page = $this->paginator->paginate(
            $log,
            $request->query->getInt('page', 1), /*page number*/
            5
        );

        $url = $request->getUri();
        $url = explode("/user", $url);
        $url = $url[0] . "/api";

        return $this->render('profile/index.html.twig', [
            'player' => $player,
            'user' => $user,
            'onlinetime' => $onlinetime,
            'logs' => $logs_page,
            'changePasswordForm' => $passwordChangeForm->createView(),
            'appUrl' => $url
        ]);
    }

    public function getFormattedTime($time)
    {
        $timePunish = new DateTime();
        $timePunish->setTimestamp(time() - $time / 1000);
        $now = new DateTime();
        $diff = $now->diff($timePunish, true);

        $timeStr = "";
        if($diff->d != 0){
            $timeStr .= $this->buildTimeSnippet($diff->d, "day", "days");
        }
        if($diff->h != 0){
            $timeStr .= $this->buildTimeSnippet($diff->h, "hour", "hours");
        }
        if($diff->i != 0){
            $timeStr .= $this->buildTimeSnippet($diff->i, "minute", "minutes");
        }

        return $timeStr;
    }

    private function buildTimeSnippet($number, $singular, $plural){
        if(is_numeric($number)){
            return ($number == 1) ? $number . " " . $this->translator->trans($singular) . " " : $number . " " . $this->translator->trans($plural) . " ";
        }
    }

    /**
     * @Route("/setup/2fa", name="setup2fa")
     */
    public function setup2FA(){
        $qrCodeContent = $this->googleAuthenticator->getQRContent($this->getUser());
        $secret = explode("?secret=", $qrCodeContent)[1];

        return $this->render('profile/setup2fa.html.twig', [
            'qr_content' => $qrCodeContent,
            'secret' => $secret
        ]);
    }

    /**
     * @Route("/setup/2fa/save/{secret}", name="save2fa")
     */
    public function save2FASecret($secret){
        $user = $this->getUser();
        $user->setAuth(true);
        $user->setAuthcode($secret);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('profile.user', [
            "user" => $user->getUsername()
        ]);
    }

    /**
     * @Route("/setup/2fa/delete", name="delete2fa")
     */
    public function disable2FA(){
        $user = $this->getUser();
        $user->setAuth(false);
        $user->setAuthcode(null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('profile.user', [
            "user" => $user->getUsername()
        ]);
    }

}
