<?php 
// app/src/AppBundle/Controller/NewsController.php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Library\Pagination\Pagination;
use AppBundle\Library\CommentsTree;
use AppBundle\Library\NewsQueue;
use AppBundle\Library\DeferredList;

class NewsController extends Controller
{
    /**
     * @Route("/news/{id}", name="news", requirements={"id": "\d+"})
     */
    public function indexAction(Request $request, $id)
    {
        $data = [];
        
        // look for news by id
        $newsRepo = $this->getDoctrine()->getRepository('AppBundle:News');
        $news = $newsRepo->findOneById($id);
        
        if (is_null($news)) {
			// i think better to do redirect with flash message
            $this->addFlash('notice', 'Статья не найдена!');
			return $this->redirectToRoute('homepage');
        }
        
        // find news author by authorId
        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $author = $userRepo->findOneById($news->getAuthorId());
        
        // find category
        $catRepo = $this->getDoctrine()->getRepository('AppBundle:Category');
        $category = $catRepo->findOneById($news->getCategoryId());
		
        // set news and author info to $data
        $data['news'] = $news;
        $data['author'] = $author;
        $data['category'] = $category;
		
		// count comments
		$comments = $this->getDoctrine()->getRepository('AppBundle:Comment')
			->findByTreeId($news->getTreeId());
		
		$data['commentsCount'] = count($comments);
				
		// sets article as viewed in users profile
		$connection = $this->getDoctrine()->getManager()->getConnection();
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			$viewedQueue = new NewsQueue($this->getUser()->getViewed());
			
			// will be added if the news doesn't exist in viewed
			// also increases total views of news
			if ($viewedQueue->add($id)) {
				$connection->beginTransaction();
				try {
					$this->getUser()->setViewed($viewedQueue->toArray());
					$news->setTotalViews($news->getTotalViews() + 1);
					$this->getDoctrine()->getManager()->flush();
					$connection->commit();
				} catch (\Doctrine\ORM\ORMException $e) {
					$connection->rollback();
				}
			}
			
			$deferredList = new DeferredList($this->getUser()->getDeferred());
			if ($deferredList->exist($id) > -1) {
				$data['isDeferred'] = 1;
			}
		} else {
			$connection->beginTransaction();
			try {
				$news->setTotalViews($news->getTotalViews() + 1);
				$this->getDoctrine()->getManager()->flush();
				$connection->commit();
			} catch (\Doctrine\ORM\ORMException $e) {
				$connection->rollback();
			}
		}
		
		return $this->render('news/index.html.twig', $data);
    }
    
    /**
     * @Route("/news/edit/{id}", name="news_edit")
     */
    public function editAction(Request $request, $id = 0)
    {
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return $this->redirectToRoute('homepage');
		}
		
        $data = [];
        
        // find all cats, tags
        $newsRepo = $this->getDoctrine()->getRepository('AppBundle:News');
        $catRepo = $this->getDoctrine()->getRepository('AppBundle:Category');
		
        // check id
        if ($id > 0) {
            // look for news by id
            $news = $newsRepo->find($id);
            
            if (is_null($news)) {
                throw $this->createNotFoundException('News you requested doesn\'t exist');
            }
            
			// checks user permission
			$isAdmin = $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN');// is user admin?
			$isAuthor = ($news->getAuthorId() == $this->getUser()->getId()); // is user the author of this article?
			
			if ( !($isAdmin || $isAuthor) ) {
				return $this->redirectToRoute('homepage'); // if he is not - redirect
			}
			
            // find news author by authorId
            $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
            $author = $userRepo->findOneById($news->getAuthorId());
            
            // find category by categoryId
            $category = $catRepo->findOneById($news->getCategoryId());
            
            // set news and author info to $data
            $data['news'] = $news;
            $data['author'] = $author;
            $data['category'] = $category;
        }
        
        $data['categories'] = $catRepo->findAll();
        $data['tags'] = SearchController::explodeTagSets($newsRepo->findTags());
		
        return $this->render('control/news.edit.html.twig', $data);
    }
}
