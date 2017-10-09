<?php
// src/AppBundle/Library/Mailing/mail_sender.php

// use Symfony\Component\HttpKernel\Kernel;

define('DS', DIRECTORY_SEPARATOR);

require __DIR__.'/../../../../app/autoload.php';

use AppBundle\Library\EMGetter;
use Symfony\Component\Yaml\Yaml;

/*
$kernel = new AppKernel('dev', true);

$response = $kernel->handle(new Symfony\Component\HttpFoundation\Request());

$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
*/

// SETUP for doctrine entity manager
$entity_dir = realpath('../../Entity');
$pathToEntities = [$entity_dir];
	/*"{$entity_dir}" . DS . "Subscriber.php",
	"{$entity_dir}" . DS . "User.php",
	"{$entity_dir}" . DS . "News.php"*/
//];

$dbConfig = Yaml::parse(
	file_get_contents(
		'..'
		. DS . '..'
		. DS . '..'
		. DS . '..'
		. DS . 'app'
		. DS . 'config'
		. DS . 'parameters.yml'
	)
);

$dbParams = [
	'driver' => 'pdo_mysql',
	'user' => $dbConfig['parameters']['database_user'],
	'password' => $dbConfig['parameters']['database_password'],
	'dbname' => $dbConfig['parameters']['database_name']
];
// END OF SETUP

$emGetter = new EMGetter(
	$pathToEntities,
	$dbParams,
	[
		[
			'name' => 'NewsPortal',
			'path' => $entity_dir
		]
	]
);
$em = $emGetter->getManager();

echo "\n".$entity_dir."\n";

file_put_contents(
	'test.txt',
	print_r(
		$em->getRepository('NewsPortal:User')->find(61),
		true
	)
);

?>