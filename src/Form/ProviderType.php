<?php

namespace App\Form;

use App\Entity\Provider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Clase de formulario para la entidad Provider.
 */
class ProviderType extends AbstractType
{
    /**
     * Define la estructura del formulario añadiendo campos que coinciden
     * con las propiedades de la entidad Provider.
     *
     * @param FormBuilderInterface $builder Objeto que ayuda a construir el formulario campo a campo.
     * @param array $options Opciones adicionales que pueden pasarse al formulario.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Campo de texto simple
            ->add('name', TextType::class, [
                'label' => 'Nombre', 
                'attr' => ['placeholder' => 'Ej.: Hoteles ViajesParaTi']
            ])

            // Campo para el correo electrónico
            ->add('email', EmailType::class, [
                'label' => 'Correo Electrónico', 
                'attr' => ['placeholder' => 'Ej.: ejemplo@ejemplo.com']
            ])

            // Campo para el teléfono
            ->add('phone', TelType::class, [
                'label' => 'Teléfono', 
                'attr' => ['placeholder' => 'Ej.: 612345678']
            ])

            /**
             * Campo de selección (Select) para el tipo de proveedor.
             * Definimos un ChoiceType manual para mapear etiquetas legibles (Hotel)
             * con los valores que se guardarán en la base de datos (hotel).
             */
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Hotel' => 'hotel',
                    'Crucero' => 'crucero',
                    'Estación de esquí' => 'esqui',
                    'Parque temático' => 'parque',
                ],
                'placeholder' => 'Selecciona un tipo',
                'label' => 'Tipo de Proveedor'
            ])

            /**
             * Campo booleano. 
             */
            ->add('active', null, [
                'label' => '¿Está activo?',
                'required' => false,
            ])
        ;
    }

    /**
     * Configura las opciones por defecto del componente de formulario.
     * * @param OptionsResolver $resolver El objeto que gestiona las opciones.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Provider::class,
        ]);
    }
}