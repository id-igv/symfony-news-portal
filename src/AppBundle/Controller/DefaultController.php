<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $data = [];
        
        // look for:
        //      - list of categories
        $catRepo = $this->getDoctrine()->getRepository('AppBundle:Category');
        $categories = $catRepo->findAll();
        
        //      - list of last N news for each category u found
        $newsRepo = $this->getDoctrine()->getRepository('AppBundle:News');
        
        $data['lastNews'] = $newsRepo->findLatestNews([], 4);
        
        foreach($categories as $category) {
            $news = $newsRepo->findLatestNews(['categoryId' => $category->getId()]);
            $data['categories'][] = [
                'category' => $category,
                'newsList' => $news
                ];
        }
        
        return $this->render('default/index.html.twig', $data);
    }
}
