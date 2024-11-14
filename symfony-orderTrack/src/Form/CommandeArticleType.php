<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\CommandeArticle;
use App\Repository\ArticleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class CommandeArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Article',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ],
                'query_builder' => function (ArticleRepository $r) {
                    return $r->createQueryBuilder('i')
                        ->orderBy('i.name', 'ASC');
                },
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('quantity', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Quantity',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'attr' => ['min' => 1],
                'required' => true,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommandeArticle::class,
        ]);
    }
}
