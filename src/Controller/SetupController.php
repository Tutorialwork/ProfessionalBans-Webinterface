<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/setup", name="setup.")
 */
class SetupController extends AbstractController
{

    /**
     * @Route("/mysql", name="setup.mysql")
     */
    public function index()
    {
        $rootDir = $this->getParameter('kernel.project_dir');
        $file = $rootDir."/.env";

        $content = file_get_contents($file);

        $content = str_replace("db_user", "TEST_USER", $content);
        $content = str_replace("db_password", "TEST_PW", $content);
        $content = str_replace("db_name", "TEST_NAME", $content);

        file_put_contents($file,$content);

        return $this->render('setup/index.html.twig', [
            'controller_name' => 'SetupController',
        ]);
    }
}
