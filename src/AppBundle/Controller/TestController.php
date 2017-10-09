<?php 
// app/src/AppBundle/Controller/NewsController.php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Library\Pagination\Pagination;
use AppBundle\Library\CommentsTree;
use AppBundle\Library\NewsQueue;
use AppBundle\Library\DeferredList;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TestController extends Controller
{
	/**
     * @Route("/test", name="test")
     */
	public function testAction(Request $request)
	{
		$data = [];
		return $this->render('test.html.twig', $data);
	}
}