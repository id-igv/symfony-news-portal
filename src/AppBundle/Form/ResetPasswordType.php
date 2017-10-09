<?php
// src/AppBundle/Form/LoginType.php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder
			->add('plainPassword', RepeatedType::class, array(
				'type' => PasswordType::class,
				'first_options' => array('label' => 'Password'),
				'second_options' => array('label' => 'Repeat Password'),
			))
			->add('submit', SubmitType::class)
		;
    }
}
    
?>