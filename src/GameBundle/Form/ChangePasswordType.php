<?php
namespace GameBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма изменения пароля
 */
class ChangePasswordType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'first_options'  => array(
                'label' => 'user.password',
                'attr' => array(
                    'autocomplete' => 'off',
                    'required' => true
                )
            ),
            'second_options' => array(
                'label' => 'user.repeatPassword',
                'attr' => array(
                    'autocomplete' => 'off',
                    'required' => true
                )
            ),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'GameBundle\Entity\User',
            'intention' => 'change',
        ));
    }

    /**
     * Возвращает название формы
     * @return string
     */
    public function getName()
    {
        return 'changePassword';
    }
}