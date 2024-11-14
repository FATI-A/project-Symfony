<?php

namespace App\Form;


use App\Entity\Commande;
use App\Entity\CommandeArticle;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'En attente',
                    'En cours' => 'En cours',
                    'Validée' => 'Validée',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'label' => 'Statut',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ]
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la commande',
                'data' => new \DateTimeImmutable(),
                'format' => 'yyyy-MM-dd',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => ['class' => 'btn btn-primary mt-4'],
            ])
        ;
        $builder->add('commandeArticles', CollectionType::class, [
            'entry_type' => CommandeArticleType::class,
            'allow_add' => true,
            'by_reference' => false,
            'attr' => [
                'class' => 'form-control',
            ],
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            'label' => 'Les articles et les quantity',
            'entry_options' => ['label' => false],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $commande = $event->getData();

            if (!$commande->getId() && $commande->getCommandeArticles()->isEmpty()) {
                $commandeArticle = new CommandeArticle();
                $commande->addCommandeArticle($commandeArticle);
            }


        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'submit_label' => 'Créer une commande'
        ]);
    }
}
