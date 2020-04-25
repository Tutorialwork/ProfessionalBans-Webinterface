<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Repository\LogRepository;
use App\Repository\ReasonRepository;
use DateTime;
use App\Repository\BansRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

    public function __construct(BansRepository $bansRepository, UserRepository $userRepository, LogRepository $logRepository, ReasonRepository $reasonRepository, HomeController $homeController, UserPasswordEncoderInterface $passwordEncoder, PaginatorInterface $paginator)
    {
        $this->bansRepository = $bansRepository;
        $this->userRepository = $userRepository;
        $this->logRepository = $logRepository;
        $this->reasonRepository = $reasonRepository;
        $this->homeController = $homeController;
        $this->passwordEncoder = $passwordEncoder;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/{user}", name="user")
     */
    public function showProfile($user, Request $request)
    {
        $player = $this->bansRepository->findOneBy(["Name" => $user]);
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

            $this->addFlash('success', 'Password successfully changed');
        }

        $logs_page = $this->paginator->paginate(
            $log,
            $request->query->getInt('page', 1), /*page number*/
            5
        );

        return $this->render('profile/index.html.twig', [
            'player' => $player,
            'user' => $user,
            'onlinetime' => $onlinetime,
            'logs' => $logs_page,
            'changePasswordForm' => $passwordChangeForm->createView()
        ]);
    }

    public function getFormattedTime($time)
    {
        $timePunish = new DateTime();
        $timePunish->setTimestamp(time() - $time / 1000);
        $now = new DateTime();
        $diff = $now->diff($timePunish, true);
        if($diff->d != 0 && $diff->h != 0 && $diff->i != 0){
            return $diff->d . " days, " . $diff->h . " hours and " . $diff->i ." minutes";
        } else if($diff->d == 0 && $diff->h != 0 && $diff->i != 0){
            return $diff->h . " hours and " . $diff->i ." minutes";
        } else if($diff->d == 0 && $diff->h == 0 && $diff->i != 0){
            return $diff->i ." minutes";
        } else if($diff->d == 0 && $diff->h != 0 && $diff->i == 0){
            return $diff->h ." hours";
        } else if($diff->d != 0 && $diff->h == 0 && $diff->i == 0){
            return $diff->d ." days";
        }
    }

}
