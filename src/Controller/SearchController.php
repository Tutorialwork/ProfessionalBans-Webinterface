<?php

namespace App\Controller;

use App\Repository\BansRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/search", name="search.")
 */
class SearchController extends AbstractController
{

    private $bansRepository;

    public function __construct(BansRepository $bansRepository)
    {
        $this->bansRepository = $bansRepository;
    }

    /**
     * @Route("/{query}", name="index")
     */
    public function index($query)
    {
        $search = $this->bansRepository->search($query);
        if(count($search) != 0){
            return $this->json([
                'results' => $search,
            ]);
        } else {
            return $this->json([
                'error' => 'No results found',
            ], 404);
        }
    }
}
