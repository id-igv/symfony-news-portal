<?php 
// app/src/AppBundle/Controller/NewsController.php

namespace AppBundle\Controller\API;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Tree;
use AppBundle\Library\CommentsTree;

class CommentController extends Controller
{
	private static $COMMENTS_PER_PAGE = 5;
	
    /**
     * @Route("news/{id}/comments", name="commentary_list", requirements={"id": "\d+"})
	 * 
	 * @return json string
     */
    public function indexAction(Request $request, $id)
    {
		$data = [];
		$status = 400;
		$page = $request->query->get('page');
		
		$manager = $this->getDoctrine()->getManager();
		$news  = $manager->getRepository('AppBundle:News')
			->find($id);
		
		if (!is_null($news)) {
			$tree = $manager->getRepository('AppBundle:Tree')
				->find($news->getTreeId());
			
			$commentsTree = new CommentsTree;
			$commentsTree->decode(json_decode($tree->getStructure()));
			$comments = [];
			
			if (!$commentsTree->isEmpty()) {
				$comments = $manager->getRepository('AppBundle:Comment')
					->findAllByArray($commentsTree->findAll());
				
				$assoComments = [];
				$users = [];
				
				foreach ($comments as $comment) {
					$assoComments[$comment->getId()] = $comment->toArray();
					$users[$comment->getUserId()] = 1;
				}
				$userArray = array_keys($users);
				
				$userArray = $manager->getRepository('AppBundle:User')
					->findAllByArray($userArray);
				
				foreach ($userArray as $user) {
					$user['url'] = $this->generateUrl(
						'profile', ['id' => $user['id']]
					);
					$users[$user['id']] = $user;
				}
				
				$this->get('logger')->info(print_r($users, true));
				
				$data['structure'] = $commentsTree;
				$data['comments'] = $assoComments;
				$data['users'] = $users;
			}
			else {
				$data['structure'] = [];
				$data['comments'] = [];
			}
			
			$status = 200;
		}
		
        return new JsonResponse(['data' => json_encode($data)], $status);
    }
	
	/**
     * @Route("/comments/add", name="comments_add")
     */
    public function addAction(Request $request)
    {
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse(['data' => json_encode([
				'errorMsg' => 'No rights!'
			])], 400);
		}
		
        $data = [];
		$status = 400;
		$_comment = $request->request->get('data');
		
		if (!is_null($_comment)) {
			$_comment = json_decode($_comment);
			
			$manager = $this->getDoctrine()->getManager();
			
			$news = $manager->getRepository('AppBundle:News')
				->find($_comment->newsId);
			
			// fills comment data
			$comment = new Comment();
			$comment->setTreeId($news->getTreeId());
			$comment->setUserId($this->getUser()->getId());
			$comment->setContent($_comment->content);
			$comment->setParentId($_comment->parentId);
			
			// saves comment to db
			try {
				$manager->persist($comment);
				$manager->flush();
			} catch(\Doctrine\ORM\ORMException $e) {
				$data = json_encode(['error' => 'Failed to save comment!']);
				return new JsonResponse(['data' => $data], $status);
			}
			
			// fills tree data
			$tree = $manager->getRepository('AppBundle:Tree')
				->find($news->getTreeId());
			
			$commentsTree = new CommentsTree;
			$commentsTree->decode(json_decode($tree->getStructure()));
			
			if ($commentsTree->add($comment->getParentId(), $comment->getId())) {
				// saves comments structure to db
				try {
					$tree->setStructure(json_encode($commentsTree));
					$manager->flush();
					$status = 200;
					$data = json_encode([
						'comment' => $comment->toArray()
					]);
				} catch(\Doctrine\ORM\ORMException $e) {
					$data = json_encode(['error' => 'Failed to save comment!']);
				}
			}
		}
		
        return new JsonResponse(['data' => $data], $status);
    }
	
    /**
     * @Route("/comments/edit/{id}", name="comments_edit")
     */
    public function editAction(Request $request, $id = 0)
    {
        return $this->render('control/news.edit.html.twig', $data);
    }
	
	/**
     * @Route("/comments/remove", name="comments_rm")
     */
    public function removeAction(Request $request)
    {
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_MOD')) {
			return new JsonResponse(['data' => json_encode([
				'errorMsg' => 'No rights!'
			])], 400);
		}
		
		$idToRm = $request->request->get('id');
		
		if (is_null($idToRm)) {
			return new JsonResponse(['data' => json_encode([
				'errorMsg' => "Comment with id = {$idToRm} doesn't exist!"
			])], 400);
		}
		
		$manager = $this->getDoctrine()->getManager();
		$comment = $manager->getRepository('AppBundle:Comment')
			->find($idToRm);
		
		if (is_null($comment)) {
			return new JsonResponse(['data' => json_encode([
				'errorMsg' => "Comment with id = {$idToRm} doesn't exist!"
			])], 400);
		}
		
		try {
			$comment->setIsRemoved(true);
			$comment->setContent('Комментарий был удален.');
			
			$manager->flush();
			
		} catch (\Doctrine\ORM\ORMException $e) {
			return new JsonResponse(['data' => json_encode([
				'errorMsg' => 'Failed to commit deleting! Retry.'
			]), 400]);
		}
		
        return new JsonResponse(['data' => json_encode([
			'comment' => $comment->toArray()
		])]);
    }
}
