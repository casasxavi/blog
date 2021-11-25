<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, array(
            "label" => "Título:",
            "attr" => array("class" => "form-control")
            ))

        ->add('description', TextareaType::class, array(
            "label" => "Descripción:",
            "attr" => array(
                "class" => "form-control",
                "cols" => 90,
                "rows" => 10,
                )
            ))

            ->add('path', FileType::class, [
                'label' => 'Imagen (archivo JPG)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/x-citrix-jpeg',
                        ],
                        'mimeTypesMessage' => 'Cargue una imagen válida JPG',
                    ])
                ],
            ])

        ->add('category', null, array(
            "label" => "Categoría:",
            "attr" => array("class" => "form-control",)
            ))
            
            ->add('tags', null, array(
                "label" => "Etiquetases:",
                "attr" => array("class" => "form-control")
                ))

        ->add('Guardar', SubmitType::class, array("attr" =>array(
            "class" => "form-submit btn btn-success",
        )))
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
