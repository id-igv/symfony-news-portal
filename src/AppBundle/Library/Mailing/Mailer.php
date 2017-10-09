<?php
// src/AppBundle/Library/Mailing/Mailer.php

namespace AppBundle\Library\Mailing;

class Mailer
{
	protected $mailer;
	protected $entityManager;
	
	public function __construct($mailer, $entityManager)
	{
		$this->mailer = $mailer;
		$this->entityManager = $entityManager;
	}
	
	public function getMailer()
	{
		return $this->mailer;
	}
	
	public function getEntityManager()
	{
		return $this->entityManager;
	}
}