<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerFormType extends AbstractType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
            ->add('first_name', TextType::class, [
            		'required' => true,
    			])
            ->add('last_name', TextType::class, [
            		'required' => true,
    			])
            ->add('date_of_birth', DateType::class, [
            		'required' => true,
    			])
            ->add('save', SubmitType::class, ['label' => 'Add Customer'])
        ;
	}
}