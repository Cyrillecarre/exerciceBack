<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Consultant;
use App\Form\ConsultantType;
use Symfony\Component\HttpFoundation\Request;

class RegisterConsultantController extends AbstractController
{
    #[Route('/register/consultant', name: 'app_register_consultant')]
    public function register(Request $request): Response
    {
        $consultant = new Consultant();
        $consultant->setRoles(['ROLE_CONSULTANT']);
        $form = $this->createForm(ConsultantType::class, $consultant); 
        $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $this->getDoctrine()->getManager()->persist($consultant);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('app_home_index');
    }

    return $this->render('register_consultant/index.html.twig', [
        'form' => $form->createView(),
    ]);
    }
}
