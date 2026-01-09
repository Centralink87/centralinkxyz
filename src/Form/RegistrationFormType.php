<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'John',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre prénom'),
                    new Length(
                        min: 2,
                        max: 100,
                        minMessage: 'Votre prénom doit contenir au moins {{ limit }} caractères',
                    ),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Doe',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre nom'),
                    new Length(
                        min: 2,
                        max: 100,
                        minMessage: 'Votre nom doit contenir au moins {{ limit }} caractères',
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'john@exemple.com',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre email'),
                    new Email(message: 'Veuillez entrer un email valide'),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'class' => 'form-input',
                        'placeholder' => '••••••••',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'class' => 'form-input',
                        'placeholder' => '••••••••',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas',
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer un mot de passe'),
                    new Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                    ),
                    new Regex(
                        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                        message: 'Le mot de passe doit comporter au moins 8 caractères et contenir des majuscules, des minuscules et des chiffres.',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
