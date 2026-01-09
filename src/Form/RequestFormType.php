<?php

namespace App\Form;

use App\Enum\CryptoType;
use App\Enum\RequestType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de demande',
                'choices' => array_combine(
                    array_map(fn(RequestType $type) => $type->label(), RequestType::cases()),
                    RequestType::cases()
                ),
                'placeholder' => 'Sélectionner un type',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez sélectionner un type de demande.'),
                ],
            ])
            ->add('cryptoType', ChoiceType::class, [
                'label' => 'Crypto',
                'choices' => array_combine(
                    array_map(fn(CryptoType $type) => $type->label(), CryptoType::cases()),
                    CryptoType::cases()
                ),
                'placeholder' => 'Sélectionner une crypto',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez sélectionner une crypto.'),
                ],
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
                'scale' => 8,
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Ex: 0.5',
                    'step' => 'any',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir un montant.'),
                ],
            ])
            ->add('publicAddress', TextType::class, [
                'label' => 'Adresse publique',
                'required' => false,
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Ex: 1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Request::class,
        ]);
    }
}

