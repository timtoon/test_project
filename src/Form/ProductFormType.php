<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductFormType extends AbstractType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
            ->add('issn', TextType::class, [
            		'required' => true,
    			])
            ->add('name', TextType::class, [
            		'required' => true,
    			])
            ->add('customer_uuid', TextType::class, [
            		'required' => true,
            		'label' => 'Customer UUID',
    			])
            ->add('save', SubmitType::class, ['label' => 'Add Product'])
        ;
	}
}