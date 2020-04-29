<?php

namespace App\Controller;

use App\Form\LanguageType;
use App\Form\MySQLType;
use App\Form\RegisterType;
use App\Repository\BansRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/setup", name="setup.")
 */
class SetupController extends AbstractController
{

    private $translator;
    private $session;
    private $passwordEncoder;
    private $bansRepository;

    public function __construct(TranslatorInterface $translator, SessionInterface $session, UserPasswordEncoderInterface $passwordEncoder, BansRepository $bansRepository)
    {
        $this->translator = $translator;
        $this->session = $session;
        $this->passwordEncoder = $passwordEncoder;
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/welcome", name="welcome")
     */
    public function index(Request $request){
        if(!$this->needSetup()){
            return $this->redirectToRoute('auth.login');
        }

        $lanuageForm = $this->createForm(LanguageType::class);
        $lanuageForm->handleRequest($request);
        if($lanuageForm->isSubmitted() && $lanuageForm->isValid()){
            $languageCode = $lanuageForm->getData()["language"];
            $rootDir = $this->getParameter('kernel.project_dir');
            $file = $rootDir."/config/packages/translation.yaml";
            $content = file_get_contents($file);

            $content = str_replace("default_locale: en", "default_locale: de", $content);

            if($languageCode == 1){
                //User selected German
                if(is_writable($file)){
                    file_put_contents($file, $content);
                    return $this->redirectToRoute('setup.mysql');
                } else {
                    $this->addFlash('error', $file." can't be written.");
                }
            } else {
                return $this->redirectToRoute('setup.mysql');
            }
        }

        return $this->render('setup/index.html.twig', [
            'version' => $_ENV["APP_VERSION"],
            'language' => $lanuageForm->createView()
        ]);
    }

    /**
     * @Route("/mysql", name="mysql")
     */
    public function mysql(Request $request)
    {
        if(!$this->needSetup()){
            return $this->redirectToRoute('auth.login');
        }

        $mysqlForm = $this->createForm(MySQLType::class);
        $mysqlForm->handleRequest($request);
        if($mysqlForm->isSubmitted() && $mysqlForm->isValid()){
            $host = $mysqlForm->get('host')->getData();
            $user = $mysqlForm->get('user')->getData();
            $name = $mysqlForm->get('database')->getData();
            $password = $mysqlForm->get('password')->getData();

            $connectionParams = array(
                'dbname' => $name,
                'user' => $user,
                'password' => $password,
                'host' => $host,
                'driver' => 'pdo_mysql',
            );
            try{
                $conn = DriverManager::getConnection($connectionParams);
                $conn->connect();

                $rootDir = $this->getParameter('kernel.project_dir');
                $file = $rootDir."/.env";

                $content = file_get_contents($file);

                $content = str_replace("127.0.0.1", $host, $content);
                $content = str_replace("db_user", $user, $content);
                $content = str_replace("db_password", $password, $content);
                $content = str_replace("db_name", $name, $content);

                if(is_writable($file)){
                    file_put_contents($file, $content);
                    $this->session->set("setup_account", true);
                    return $this->redirectToRoute('setup.account');
                } else {
                    $this->addFlash('error', $file." can't be written.");
                }
            } catch (DBALException $e) {
                $this->addFlash('error', $this->translator->trans('mysql_fail'));
            }
        }

        return $this->render('setup/mysql.html.twig', [
            'mysql' => $mysqlForm->createView(),
        ]);
    }

    /**
     * @Route("/account", name="account")
     */
    public function account(Request $request){
        if(!$this->session->get("setup_account")){
            return $this->redirectToRoute('auth.login');
        }

        $accountForm = $this->createForm(RegisterType::class);
        $accountForm->handleRequest($request);
        if($accountForm->isSubmitted() && $accountForm->isValid()){
            $player = $this->bansRepository->findOneBy(["Name" => $accountForm->get('username')->getData()]);
            if($player){
                $user = $accountForm->getData();
                $roles = $user->getRoles();
                array_push($roles, "ROLE_SUPER_ADMIN");
                $user->setRoles($roles);
                $user->setUUID($player->getUUID());
                $user->setAuth(0);
                $pwHash = $this->passwordEncoder->encodePassword($user, $user->getPassword());
                $user->setPassword($pwHash);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('auth.login');
            } else {
                $this->addFlash('error', 'Please join first the Minecraft server');
            }
        }

        return $this->render('setup/account.html.twig', [
            'account' => $accountForm->createView(),
        ]);
    }

    public function needSetup(){
        $em = $this->getDoctrine()->getManager();
        try{
            $em->getConnection()->connect();
            return false;
        } catch (ConnectionException $e){
            return true;
        }
    }
}
