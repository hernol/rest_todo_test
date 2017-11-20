<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use AppBundle\Form\Type\BooleanType;

/**
 * Class TodoType
 * @package AppBundle\Form
 */
class TodoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class)
                ->add('description', TextareaType::class)
                ->add('due_date', DateTimeType::class, array('widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->add('completed', BooleanType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'AppBundle\Document\Todo',
                'csrf_protection' => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName ()
    {
        return 'todobundle_todo';
    }
}