<?php

namespace App\Form;

use App\Entity\Candidat;
use App\Entity\Recruteur;
use App\Entity\Consultant;
use App\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RoleRepository;


class RegistrationFormType extends AbstractType
{
    private $roles;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roles = $roleRepository->findAll();
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => $this->getRolesChoices(),
                'mapped' => false,
            ])
        ;

        
        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SET_DATA, function (\Symfony\Component\Form\FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data && $data['role'] instanceof Candidat) {
                $form->add('cv', FileType::class, [
                    'label' => 'CV (PDF)',
                    'mapped' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                            'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide',
                        ]),
                    ],
                ]);
            }
    });
    
        }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }

    private function getRolesChoices(): array
    {
        $choices = [];
        foreach ($this->roles as $role) {
            $choices[$role->getName()] = $role;
        }
        return $choices;
    }
}
