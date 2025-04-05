<?php

namespace GrinWay\Telegram\Form;

use GrinWay\Telegram\Form\DataTransformer\TelegramLabeledPricesDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TelegramLabeledPricesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new TelegramLabeledPricesDataTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => TelegramLabeledPriceFormType::class,
        ]);
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}
