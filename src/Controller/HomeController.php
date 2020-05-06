<?php

namespace App\Controller;

use App\Entity\Log;
use App\Repository\BansRepository;
use App\Repository\LogRepository;
use App\Repository\ReasonRepository;
use App\Repository\UserRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/", name="home.")
 */
class HomeController extends AbstractController
{

    private $bansRepository;
    private $userRepository;
    private $logRepository;
    private $translator;
    private $reasonRepository;
    private $paginator;

    public function __construct(BansRepository $bansRepository, UserRepository $userRepository, LogRepository $logRepository, ReasonRepository $reasonRepository, TranslatorInterface $translator, PaginatorInterface $paginator)
    {
        $this->bansRepository = $bansRepository;
        $this->userRepository = $userRepository;
        $this->logRepository = $logRepository;
        $this->translator = $translator;
        $this->reasonRepository = $reasonRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $bans = $this->bansRepository->findBy(['Banned' => 1]);
        $mutes = $this->bansRepository->findBy(["Muted" => 1]);
        $onlinePlayers = $this->bansRepository->findBy(["OnlineStatus" => 1]);
        $players = $this->bansRepository->findAll();
        $teammembers = $this->userRepository->findAll();
        $logs = $this->logRepository->findBy([], ['Date' => 'DESC']);

        $onlinetime = 0;
        foreach ($players as $player){
            $onlinetime += $player->getOnlinetime();
        }

        $today = 0;
        $diff = time() - 86400;
        foreach ($logs as $log){
            $uuidPlayer = $this->bansRepository->findOneBy(["UUID" => $log->getUUID()]);
            $teamuuidPlayer = $this->bansRepository->findOneBy(["UUID" => ($log->getByUUID() != null) ? $log->getByUUID() : 'random']);

            $integerCheck = (int) $log->getNote();
            if($integerCheck != 0){
                $reason = $this->reasonRepository->findOneBy(["id" => $integerCheck]);
                if($reason){
                    $log->setNote($reason->getReason());
                } else {
                    $log->setNote($this->translator->trans('deleted_reason')." (#".$integerCheck.")");
                }
            }

            $log->setUUID($uuidPlayer->getName());
            $log->setByUUID(($teamuuidPlayer != null) ? $teamuuidPlayer->getName() : '');
            $log->setAction(str_replace("%text%", $log->getNote(), $this->resolveAction($log)));
            $log->setDate(round($log->getDate() / 1000));

            if($log->getDate() > $diff){
                $today++;
            }
        }

        $log_page = $this->paginator->paginate(
            $logs,
            $request->query->getInt('page', 1), /*page number*/
            10
        );

        return $this->render('home/index.html.twig', [
            'bans_count' => count($bans),
            'mute_count' => count($mutes),
            'online_players' => count($onlinePlayers),
            'players' => count($players),
            'onlinetime' => $this->getFormattedTime($onlinetime),
            'teammembers' => count($teammembers),
            'logs' => $log_page,
            'punished_today' => $today
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

    public function resolveAction(Log $log){
        switch ($log->getAction()){
            case 'BAN':
                return $this->translator->trans("event_BAN");
                break;
            case 'MUTE':
                return $this->translator->trans("event_MUTE");
                break;
            case 'ADD_WORD_BLACKLIST':
                return $this->translator->trans("event_ADD_WORD_BLACKLIST");
                break;
            case 'DEL_WORD_BLACKLIST':
                return $this->translator->trans("event_DEL_WORD_BLACKLIST");
                break;
            case 'CREATE_CHATLOG':
                return $this->translator->trans("event_CREATE_CHATLOG");
                break;
            case 'IPBAN_IP':
                return $this->translator->trans("event_IPBAN_IP");
                break;
            case 'IPBAN_PLAYER':
                return $this->translator->trans("event_IPBAN_PLAYER");
                break;
            case 'KICK':
                return $this->translator->trans("event_KICK");
                break;
            case 'REPORT':
                return $this->translator->trans("event_REPORT");
                break;
            case 'REPORT_OFFLINE':
                return $this->translator->trans("event_REPORT_OFFLINE");
                break;
            case 'REPORT_ACCEPT':
                return $this->translator->trans("event_REPORT_ACCEPT");
                break;
            case 'UNBAN_IP':
                return $this->translator->trans("event_UNBAN_IP");
                break;
            case 'UNBAN_BAN':
                return $this->translator->trans("event_UNBAN_BAN");
                break;
            case 'UNBAN_MUTE':
                return $this->translator->trans("event_UNBAN_MUTE");
                break;
            case 'ADD_WEBACCOUNT':
                return $this->translator->trans("event_ADD_WEBACCOUNT");
                break;
            case 'DEL_WEBACCOUNT':
                return $this->translator->trans("event_DEL_WEBACCOUNT");
                break;
            case 'AUTOMUTE_ADBLACKLIST':
                return $this->translator->trans("event_AUTOMUTE_ADBLACKLIST");
                break;
            case 'AUTOMUTE_BLACKLIST':
                return $this->translator->trans("event_AUTOMUTE_BLACKLIST");
                break;
        }
    }
}
