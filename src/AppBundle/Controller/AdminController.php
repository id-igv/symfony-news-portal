<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Library\Pagination\Pagination;

class AdminController extends Controller
{
    /**
     * @Route("/control", name="control_main")
     */
    public function indexAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			return $this->redirectToRoute('homepage');
		}
		
        return $this->render('control/base.html.twig');
    }
    
    /**
     * @Route("/control/categories", name="control_categories")
     */
    public function categoriesAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			return $this->redirectToRoute('homepage');
		}
		
        $data = [];
        
        $catRepo = $this->getDoctrine()->getRepository('AppBundle:Category');
        $data['categories'] = $catRepo->findAll();
        
        return $this->render('control/categories.html.twig', $data);
    }
    
    /**
     * @Route("/control/news", name="control_news")
     */
    public function newsAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			return $this->redirectToRoute('homepage');
		}
		
        $data = [];
		$NEWS_PER_PAGE = $request->query->get('count');
		if (is_null($NEWS_PER_PAGE) || $NEWS_PER_PAGE < 5) {
			$NEWS_PER_PAGE = 15;
		}
		
		$page = $request->query->get('page');
		if(is_null($page)) {
			$page = 1;
		}
        
        $newsRepo = $this->getDoctrine()->getRepository('AppBundle:News');
        
		$newsCount = count($newsRepo->findAll());
		$data['newsList'] = $newsRepo->findBy([], [], $NEWS_PER_PAGE, ($page - 1) * $NEWS_PER_PAGE);
        
        $pagination = new Pagination([
            'itemsCount' => $newsCount,
            'itemsPerPage' => $NEWS_PER_PAGE,
            'currentPage' => $page
        ]);
        
		$data['getParams'] = '';
		$data['newsCount'] = $newsCount;
        $data['pagination'] = $pagination;
        $data['btnCount'] = count($pagination->buttons);
        
        return $this->render('control/news.html.twig', $data);
    }
	
	/**
     * @Route("/control/users", name="control_users")
     */
    public function usersAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			return $this->redirectToRoute('homepage');
		}
		
        $data = [];
        
		$ITEMS_PER_PAGE = $request->query->get('count');
		if (is_null($ITEMS_PER_PAGE) || $ITEMS_PER_PAGE < 5) {
			$ITEMS_PER_PAGE = 15;
		}
		
		$page = $request->query->get('page');
		if(is_null($page)) {
			$page = 1;
		}
        
        $usersRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        
		$usersCount = count($usersRepo->findAll());
		$data['users'] = $usersRepo->findBy([], [], $ITEMS_PER_PAGE, ($page - 1) * $ITEMS_PER_PAGE);
        
        $pagination = new Pagination([
            'itemsCount' => $usersCount,
            'itemsPerPage' => $ITEMS_PER_PAGE,
            'currentPage' => $page
        ]);
        
		// gets list of roles
		$roles = array_keys($this->getParameter('security.role_hierarchy.roles'));
		for ($i = 0, $len = sizeof($roles); $i < $len; $i++) {
			$roles[$i] = str_replace('ROLE_', '', $roles[$i]);
		}
		
		$data['roles'] = $roles;
		$data['getParams'] = '';
		$data['usersCount'] = $usersCount;
        $data['pagination'] = $pagination;
        $data['btnCount'] = count($pagination->buttons);
        
        return $this->render('control/users.html.twig', $data);
    }
}
?>