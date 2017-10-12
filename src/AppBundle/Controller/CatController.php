<?php
// src/AppBundle/Controller/API/CategoryController.php

namespace AppBundle\Controller\API;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Category;
class CategoryController extends Controller {
    /**
     * @Route("/api/category/add", name="api_category_add")
     *
     * @return JSON
     */
    public function addAction(Request $request)
    {
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_EDITOR')) {
			return new JsonResponse([], 400);
		}
		
        $catName = $request->request->get('name');
        $data = [];
        $status = 400;
        
        if (!is_null($catName)) {
            $category = new Category();
            $category->setName($catName);
            
            $catManager = $this->getDoctrine()->getManager();
            $catRepo = $this->getDoctrine()->getRepository('AppBundle:Category');
            
            try {
                $catManager->persist($category);
                $catManager->flush();
                
                $category = $catRepo->findOneByName($catName);
                
                if (!is_null($category)) {
                    $data = json_encode([
                        'id' => $category->getId(),
                        'name' => $category->getName()
                    ]);
                    
                    $status = 200;
                }
            } 
            catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $status = 400;
            }
            catch (\Doctrine\ORM\ORMException $e) {
                $status = 400;
            }
        }
        
        return new JsonResponse(['data' => $data], $status);
    }
    
    /**
     * @Route("/api/category/delete", name="api_category_delete")
     *
     * @return JSON
     */
    public function deleteAction(Request $request)
    {
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_EDITOR')) {
			return new JsonResponse([], 400);
		}
		
        $catId = $request->request->get('id');
        $data = [];
        $status = 400;
        
        if (!is_null($catId)) {
            $catManager = $this->getDoctrine()->getManager();
            $category = $catManager->getRepository('AppBundle:Category')->find($catId);
            if (!is_null($category)) {
                try {
                    $catManager->remove($category);
                    $catManager->flush();
                    
                    $data = json_encode([
                        'id' => $catId
                    ]);
                    
                    $status = 200;
                }
                catch (\Doctrine\ORM\ORMException $e) {
                    $status = 400;
                }
            }
        }
        
        return new JsonResponse(['data' => $data], $status);
    }
    
    /**
     * @Route("/api/category/update", name="api_category_update")
     *
     * @return JSON
     */
    public function updateAction(Request $request)
    {
		if (!$this->get('security.authorization_checker')->isGranted('ROLE_EDITOR')) {
			return new JsonResponse([], 400);
		}
		
        $catId = $request->request->get('id');
        $catName = $request->request->get('name');
        $data = [];
        $status = 400;
        
        $catManager = $this->getDoctrine()->getManager();
        $category = $catManager->getRepository('AppBundle:Category')->find($catId);
        
        if (!is_null($category)) {
            $category->setName($catName);
            try {
                $catManager->flush();
                
                $data = [
                    'id' => $catId,
                    'name' => $catName
                ];
                $status = 200;
            } catch (\Doctrine\ORM\ORMException $e) {
                $status = 400;
            }
            
        }
        
        return new JsonResponse(['data' => json_encode($data)], $status);
    }
}
?>
