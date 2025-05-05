<?php
// src/Form/ProduitType.php
namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, ['attr' => ['class' => 'form-control']])
        ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control']])
        ->add('price', NumberType::class, ['attr' => ['class' => 'form-control']])
        ->add('quantity', NumberType::class, ['attr' => ['class' => 'form-control']])
        ->add('image', TextType::class, [
            'attr' => ['class' => 'form-control'],
            'label' => 'Image URL or Path'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}