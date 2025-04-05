<?php

namespace GrinWay\Telegram\Form;

use GrinWay\Telegram\Form\DataTransformer\TelegramLabeledPriceDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TelegramLabeledPriceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label')
            ->add('amount', NumberWithEndFiguresFormType::class)//
        ;

        $builder->addModelTransformer(new TelegramLabeledPriceDataTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
