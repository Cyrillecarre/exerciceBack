<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Recruiters;
use App\Form\RecruitersType;
use Symfony\Component\HttpFoundation\Request;

class RegisterRecruiterController extends AbstractController
{
    #[Route('/register/recruiters', name: 'app_register_recruiters')]
    public function register(Request $request): Response
    {
        $recruiters = new Recruiters();
        $recruiters->setRoles(['ROLE_RECRUITER']);
        $form = $this->createForm(RecruitersType::class, $recruiters); 
        $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $this->getDoctrine()->getManager()->persist($recruiters);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('app_home_index');
    }

    return $this->render('register_consultant/index.html.twig', [
        'form' => $form->createView(),
    ]);
    }
}
