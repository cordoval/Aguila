<?php

namespace Storm\AguilaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Storm\AguilaBundle\Entity\Task;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('description')
            ->add('difficulty', 'choice', array(
                'choices' => Task::$difficulty_choices,
            ))
            ->add('priority', 'choice', array(
                'choices' => Task::$priority_choices,
            ))
            ->add('assignee', null, array(
                'required' => false,
            ))
            ->add('reporter')
            ->add('comments', null, array(
                'required' => false,
            ))
            ->add('issues', null, array(
                'required' => false,
            ))
            ->add('feature')
        ;
    }

    public function getName()
    {
        return 'storm_aguilabundle_tasktype';
    }
}