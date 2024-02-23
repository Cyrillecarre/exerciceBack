<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Consultant;
use App\Entity\Recruteur;
use App\Entity\Role;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\RoleRepository;



class EnregistrementController extends AbstractController
{
    
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
    $this->entityManager = $entityManager;
    $this->passwordHasher = $passwordHasher;
    }

    /**
     * @Route("/Enregistrement", name="app_register")
     */
    
    public function enregistrement(Request $request, RoleRepository $roleRepository)
    {
        $roles = $roleRepository->findAll();

        // Créer le formulaire d'inscription en passant les rôles
        $form = $this->createForm(RegistrationFormType::class, null, ['roles' => $roles]);
        
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userData = $form->getData();

            // Choix de l'entité en fonction du rôle sélectionné
            switch ($userData['role']) {
                case 'candidat':
                    $user = new Candidat();
                    break;
                case 'consultant':
                    $user = new Consultant();
                    break;
                case 'recruteur':
                    $user = new Recruteur();
                    break;
                default:
                    throw new \Exception('Invalid role');
            }

            // Encodage du mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);

            $user->setPassword($hashedPassword);

            // Traitement du CV si c'est un candidat
            if ($user instanceof Candidat && isset($userData['cv'])) {
                $cvFile = $userData['cv'];
                $fileName = uniqid() . '.' . $cvFile->guessExtension();
                $cvFile->move($this->getParameter('upload_directory'), $fileName);
                $user->setCvPdf($fileName);
            }

            // Enregistrement de l'utilisateur
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('enregistrement/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
