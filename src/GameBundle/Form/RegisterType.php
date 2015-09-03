<?php

namespace GameBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма регистрации
 */
class RegisterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array(
                'label' => 'user.username',
            ))
            ->add('plainPassword', 'repeated', array(
                "type" => "password",
                'first_options' => array(
                    'label' => 'user.password',
                    'attr' => array(
                        'placeholder' => 'user.password',
                        'autocomplete' => 'off',
                        'required' => true
                    )
                ),
                'second_options' => array(
                    'label' => 'user.repeatPassword',
                    'attr' => array(
                        'placeholder' => 'user.repeatPassword',
                        'autocomplete' => 'off',
                        'required' => true
                    )
                ),
            ))
            ->add('email', null, array(
                'label' => 'user.email',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'GameBundle\Entity\User',
            'intention' => 'register',
        ));
    }

    /**
     * Возвращает название формы
     * @return string
     */
    public function getName()
    {
        return 'register';
    }

}