<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password',PasswordType::class,array(
                'required' => false,
                'label' => 'user.create.password',
                'attr' => array(
                    'class' => ''
                )
            ))
            ->add('mail',EmailType::class,array(
                'label' => 'user.create.mail',
                'attr' => array(
                    'class' => ''
                )
            ))
            ->add('firstname',TextType::class,array(
                'label' => 'user.create.firstname',
                'attr' => array(
                    'class' => ''
                )
            ))
            ->add('lastname',TextType::class,array(
                'label' => 'user.create.lastname',
                'attr' => array(
                    'class' => ''
                )
            ))
            ->add('preferedPlatform', ChoiceType::class,array(
                'label' => 'user.profile.platformePrefered',
                'mapped' => false,
                'choices' => array(
                    "Aucune" => "",
                    "Spotify" => "spotify",
                    "Deezer" => "deezer"
                ),
                'attr' => array(
                    'class' => ''
                )
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            'translation_domain' => 'forms'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user';
    }


}
