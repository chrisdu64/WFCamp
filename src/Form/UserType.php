<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email')
        ->add('username', TextType::class, [
        ])
        ->add('birthdate', BirthdayType::class,
        [
            'widget' => 'single_text',
            'input' => 'datetime_immutable',
        ])
        ->add('createdAt', DateTimeType::class, [
            'disabled' => true,
            'date_widget' => 'single_text',
            'input' => 'datetime_immutable',
            'time_widget' => 'single_text',
        ])
        ->add('mediaFile', VichFileType::class, [
            'required' => false,
            'allow_delete' =>true,
            'delete_label' =>'Remove Image',
            'asset_helper' => true,
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
