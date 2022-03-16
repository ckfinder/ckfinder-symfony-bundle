<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class PlaygroundController extends AbstractController
{
    public function __invoke(): Response
    {
        $form = $this->createFormBuilder()
            ->getForm();

        return $this->render('playground.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
