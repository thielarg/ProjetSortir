<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site', EntityType::class,
                ['class' => 'App\Entity\Site',
                    'label' => 'Ville de rattachement : ',
                    'choice_label' => 'nom',
                    'placeholder' => '-- Choisir un site',
                    'required' => true,
            ])
            ->add('username', TextType::class, [
                'label' => 'Pseudo : ',
                'attr'=>[
                    'class'=>'form-control'
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom : ',
                'attr'=>[
                    'class'=>'form-control'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom : ',
                'attr'=>[
                    'class'=>'form-control'
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone : ',
                'attr'=>[
                    'class'=>'form-control'
                ]
            ])
            ->add('mail', TextType::class, [
                'label' => 'Email : ',
                'attr'=>[
                    'class'=>'form-control'
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (PNG, JPG, BMP)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/*'
                        ],
                        'mimeTypesMessage' => 'Merci de sélectionner un fichier image.',
                    ])
                ],
                'attr'=>[
                    'class'=>'form-control'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Vous n\'avez pas saisi le même mot de passe',
                'first_options' => [
                    'label' => 'Mot de passe : ',
                    'attr'=>[
                        'class'=>'form-control'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmation : ',
                    'attr'=>[
                        'class'=>'form-control'
                    ]
                ]
            ])
            //->add('administrateur')
            //->add('actif')
            //->add('roles')
            //->add('resetToken')
            //->add('isVerified')
           // ->add('sorties')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
