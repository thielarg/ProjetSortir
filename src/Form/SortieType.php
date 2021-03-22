<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvents;
use Zend\EventManager\Event;


class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie :'
            ])
            ->add('dateHeureDebut', DateTimeType::class, [

            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée :'
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [

            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places :'
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos :'
            ])
            ->add('ville', EntityType::class,[
                    'class' => 'App\Entity\Ville',
                    'mapped' => false,
                    'choice_label' => 'nom',
                    'placeholder' => 'Selectionner une ville',
                    'required' => false
                ]
            )
        ;

        $builder->get('ville')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event){
                $form = $event->getForm();
                $this->addLieuField($form->getParent(), $form->getData());
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event){
                $data = $event->getData();
                /* @var $lieu \App\Entity\Lieu */
                $lieu = $data->getLieu();
                $form = $event->getForm();
                if($lieu){
                    $ville = $lieu->getVille();
                    $this->addLieuField($form, $ville);
                    $form->get('ville')->setData($ville);
                }else{
                   $this->addLieuField($form, null);
                }

            }
        );
    }

    private function addLieuField(FormInterface $form, ?Ville $ville){
        $builder = $form->add('lieu', EntityType::class,[
            'class' => Lieu::class,
            'choice_label' => 'nom',
            'placeholder' => $ville ? 'Selectionnez votre lieu' : 'Selectionnez votre ville',
            'required' => true,
            'auto_initialize' => false,
            'choices' => $ville ? $ville->getLieux() : []
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
