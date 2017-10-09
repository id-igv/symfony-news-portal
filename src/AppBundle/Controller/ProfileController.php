<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Form\ResetMailType;

class ProfileController extends Controller
{
    /**
     * @Route("/users/{id}", name="profile")
     */
    public function indexAction(Request $request, $id)
    {
        $data = []; // will be sent to render
        
        $manager = $this->getDoctrine()->getManager();
        $data['user'] = $manager->getRepository('AppBundle:User')
            ->find($id);
		
		if (!$data['user']) {
			$this->addFlash('notice', 'Пользователь не найден!');
			return $this->redirectToRoute('homepage');
		}
		
		// get deferred news
		$data['deferred'] = $manager->getRepository('AppBundle:News')
			->findAllByArray($data['user']->getDeferred());
		
		
		// $data['crntPage'] = 'main';
		
        return $this->render('profile/index.html.twig', $data);
    }
    
	/**
     * @Route("/users/{id}/history", name="profile_history")
     */
    public function historyAction(Request $request, $id)
	{
		$data = []; // will be sent to render
        
        $manager = $this->getDoctrine()->getManager();
        $data['user'] = $manager->getRepository('AppBundle:User')
            ->find($id);
        
		// get viewed news
		$data['viewed'] = $manager->getRepository('AppBundle:News')
			->findAllByArray($data['user']->getViewed());
		
		// $data['crntPage'] = 'history';
		
        return $this->render('profile/history.html.twig', $data);
	}
	
	/**
     * @Route("/users/{id}/news", name="profile_news")
     */
    public function newsAction(Request $request, $id)
	{
		$data = []; // will be sent to render
        
        $manager = $this->getDoctrine()->getManager();
        $data['user'] = $manager->getRepository('AppBundle:User')
            ->find($id);
		
		$data['news'] = $manager->getRepository('AppBundle:News')
            ->findBy(['authorId' => $id]);
		
        return $this->render('profile/news.html.twig', $data);
	}
	
	/**
     * @Route("/users/{id}/comments", name="profile_comm")
     */
    public function commentsAction(Request $request, $id)
	{
		$data = []; // will be sent to render
        
        $manager = $this->getDoctrine()->getManager();
        $data['user'] = $manager->getRepository('AppBundle:User')
            ->find($id);
			
		$data['comments'] = $manager->getRepository('AppBundle:Comment')
			->findBy(['userId' => $id], ['date' => 'DESC']);
		
		return $this->render('profile/comments.html.twig', $data);
	}
	
    /**
     * @Route("/users/{id}/edit", name="profile_edit")
     */
    public function editAction(Request $request, $id)
    {
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return $this->redirectToRoute('homepage');
		}
		
		$user = $this->getUser();
		
		$data['user'] = $user;
		$data['avatars'] = null;
		
		// deals with subscribtion
		$data['subList'] = [];
		$data['categories'] = $this->getDoctrine()->getRepository('AppBundle:Category')
			->findAll();
		
		$sub = $this->getDoctrine()->getRepository('AppBundle:Subscriber')
			->findOneByEmail(
				$user->getEmail()
			);
		
		if ($sub) {
			$data['subList'] = explode(';', $sub->getCategoryList());
		}
        
        return $this->render('profile/edit.html.twig', $data);
    }
	
	/**
     * @Route("/users", name="profiles_list")
     */
    public function listAction(Request $request)
	{
		$data = []; // will be sent to render
        
        $manager = $this->getDoctrine()->getManager();
        $data['users'] = $manager->getRepository('AppBundle:User')
            ->findAll();
		
		return $this->render('profile/list.html.twig', $data);
	}
}