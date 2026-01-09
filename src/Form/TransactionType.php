<?php

namespace App\Form;

use App\Entity\Transaction;
use App\Enum\CryptoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cryptoType', ChoiceType::class, [
                'label' => 'Crypto',
                'choices' => array_combine(
                    array_map(fn(CryptoType $type) => $type->label(), CryptoType::cases()),
                    CryptoType::cases()
                ),
                'placeholder' => 'Sélectionner une crypto',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
                'scale' => 8,
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Ex: 0.5',
                    'step' => 'any',
                ],
            ])
            ->add('entryPrice', NumberType::class, [
                'label' => 'Prix d\'entrée ($)',
                'scale' => 8,
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Ex: 42000.00',
                    'step' => 'any',
                ],
            ])
            ->add('transactionDate', DateTimeType::class, [
                'label' => 'Date de la transaction',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}

