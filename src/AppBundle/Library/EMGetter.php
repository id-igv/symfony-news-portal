<?php
// src/AppBundle/Library/Mailing/Mailer.php

namespace AppBundle\Library;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class EMGetter
{
	protected $pathToEntities;
	protected $isDevMod;
	protected $dbParams;
	
	protected $config;
	protected $entityManager;
	
	public function __construct($pathToEntities, $dbParams, $namespaces, $isDevMod = true)
	{
		$this->pathToEntities = $pathToEntities;
		$this->isDevMod = $isDevMod;
		$this->dbParams = $dbParams;
		
		$this->config = Setup::createAnnotationMetadataConfiguration(
			$pathToEntities,
			$isDevMod
		);
		
		foreach ($namespaces as $namespace) {
			$this->config->addEntityNamespace($namespace['name'], $namespace['path']);
		}
		
		$this->entityManager = EntityManager::create(
			$dbParams,
			$this->config
		);
	}
	
	public function getManager()
	{
		return $this->entityManager;
	}
	
	public function getConfig()
	{
		return $this->config;
	}
}