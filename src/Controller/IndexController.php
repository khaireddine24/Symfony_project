<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/home/{name}', name: 'app_homepage')]
    public function home(string $name): Response
    {
        return $this->render('index/index.html.twig', [
            'message' => 'Ma première page Symfony',
            'name' => $name
        ]);
    }
}
