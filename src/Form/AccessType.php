<?php
/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

 declare( strict_types = 1 );
 
namespace CoreSys\UserManagement\Form;

use CoreSys\UserManagement\Entity\Access;
use CoreSys\UserManagement\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('path', TextType::class, ['required' => true])
            ->add('ips', TextType::class, ['required' => false])
            ->add('port', NumberType::class, ['required' => false])
            ->add('host', TextType::class, ['required' => false])
            ->add('methods', ChoiceType::class, [
                'required' => false,
                'choices' => $this->getMethodChoices(),
                'multiple' => true,
            ])
            ->add('attributes', TextType::class, ['required' => false])
            ->add('route', TextType::class, ['required' => false])
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('mandatory', CheckboxType::class, ['required' => false])
            ->add('roles', EntityType::class, [
                'required' => false,
                'class' => Role::class,
                'choice_label' => 'name',
                'multiple' => true
            ]);
    }

    protected function getMethodChoices(): array
    {
        $choices = [];

        foreach (Access::METHODS as $method) {
            $choices[$method] = $method;
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Access::class,
        ]);
    }
}
