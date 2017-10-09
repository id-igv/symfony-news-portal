<?php 
// app/src/AppBundle/Controller/API/NewsController.php

namespace AppBundle\Controller\API;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\News;
use AppBundle\Entity\Tree;
use AppBundle\Library\Uploader;

class NewsController extends Controller
{
    /**
     * @Route("/api/news/add", name="api_news_add")
     */
    public function addAction(Request $request)
    {
        echo "<pre>";
        var_dump($request->request);
        die;
        
        $data = [];
        $status = 400;
        return new JsonResponse($data, $status);
    }
    
    /**
     * @Route("/api/news/delete", name="api_news_delete")
     */
    public function deleteAction(Request $request)
    {
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			return new JsonResponse([
				'data' => json_encode(['errorMsg' => 'Авторизируйтесь!'])
			], 400);
		}
		
        $newsId = $request->request->get('id');
        $data = [];
        $status = 400;
        
        if (!is_null($newsId)) {
            $newsManager = $this->getDoctrine()->getManager();
            $news = $newsManager->getRepository('AppBundle:News')->find($newsId);
            
            if (!is_null($news)) {
                try {
                    $newsManager->remove($news);
                    $newsManager->flush();
                    
                    $status = 200;
                    $data = json_encode([
                        'id' => $newsId
                    ]);
                } catch (\Doctrine\ORM\ORMException $e) {
                    $status = 400;
                }
                
            }
        }
        
        return new JsonResponse(['data' => $data], $status);
    }
    
    /**
     * @Route("/api/news/update", name="api_news_update")
     */
    public function updateAction(Request $request)
    {
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_AUTHOR')) {
			return new JsonResponse([
				'data' => json_encode(['errorMsg' => 'Авторизируйтесь!'])
			], 400);
		}
		
        $_news = $request->request->get('data');
        $data = [];
        $status = 400;
        
        if (!is_null($_news)) {
            $_news = json_decode($_news);
            
            $manager = $this->getDoctrine()->getManager();
            
            $news = $manager->getRepository('AppBundle:News')->find($_news->id);
            
            if (is_null($news)) {
                $news = new News();
            } else {
				$imagePath = '';
				if (false !== ($imagePath = $this->get('app.uploader')->uploadFromUrl($_news->titleImage, "news_title_image_id".$news->getId()))) {
					$news->setTitleImage($imagePath);
				}
			}
            
            if ($news->getTitle() != $_news->title) {
                $news->setTitle($_news->title);
            }
            
            if ($news->getCategoryId() != $_news->categoryId) {
                $news->setCategoryId($_news->categoryId);
            }
            
            if ($news->getAuthorId() != $_news->authorId) {
                $news->setAuthorId($_news->authorId);
            }
			
            if ($news->getContent() != $_news->content) {
                $news->setContent($_news->content);
            }
            
            if ($news->getTagSet() != $_news->tagSet) {
                $news->setTagSet($_news->tagSet);
            }
            
            if ($_news->id == 0) {				
				try {
					$tree = new Tree;
					
					$manager->getConnection()->beginTransaction();
					
					$manager->persist($tree);
					$manager->flush();
					
					$news->setTreeId($tree->getId());
					$news->setCreated(time());
					$manager->persist($news);
					$manager->flush();
					
					$imagePath = '';
					if (false !== ($imagePath = $this->get('app.uploader')->uploadFromUrl($_news->titleImage, "news_title_image_id".$news->getId()))) {
						$news->setTitleImage($imagePath);
					}
					$manager->persist($news);
					$manager->flush();
					
					$manager->getConnection()->commit();
					
					$status = 200;
					$data = json_encode([
						'newsId' => $news->getId(),
						'newsLink' => $this->generateUrl('news', ['id' => $news->getId()])
					]);
					
				} catch (\Exception $e) {
					// roolback changes
					$manager->getConnection()->rollback();
					$data = json_encode([
						'errorMsg' => 'Ошибка при сохранении!'
					]);
				}
            } else {
				try {
					$manager->flush();
					
					$status = 200;
					$data = json_encode([
						'newsId' => $news->getId(),
						'newsLink' => $this->generateUrl('news', ['id' => $news->getId()])
					]);
				} catch (\Doctrine\ORM\ORMException $e) {
					$status = 400;
					$data = json_encode([
						'errorMsg' => 'Ошибка при сохранении!'
					]);
				}
			}
			
			
        }
        
        return new JsonResponse(['data' => $data], $status);
    }
    
    /**
     * @Route("/api/upload", name="news_upload_image")
     * 
     * @return JSON {"name": "temp_uploaded_file_name"}
     */
    public function uploadImageAction(Request $request)
    {
        $data = [];
        $status = 400;
        return new JsonResponse($data, $status);
    }
	
}
?>