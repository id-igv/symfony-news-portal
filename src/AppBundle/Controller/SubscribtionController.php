<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Subscriber;

class SubscribtionController extends Controller
{
	/**
     * @Route("/subscribtion", name="form_subscribtion")
     */
    public function indexAction(Request $request)
	{
		$data = [];
		
		$data['categories'] = $this->getDoctrine()->getRepository('AppBundle:Category')
			->findAll();
		
		return $this->render('default/form.subscribe.html.twig', $data);
	}
	
    /**
     * @Route("/subscribe", name="subscribe")
     */
    public function subscribeAction(Request $request)
    {
		$email = $request->request->get('email');
		$categories = $request->request->get('categories');
		
		if (!$email
			|| !$categories) {
				
			$this->addFlash('error', 'Неверно указаны данные!');
			return $this->redirectToRoute('homepage');
		}
		
		$sub = new Subscriber();
		$sub->setEmail($email);
		$sub->setCategoryList($categories);
		
		try {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($sub);
			$manager->flush();
			$this->addFlash('notice', 'Успешно подписаны!');
		} catch (\Exception $e) {
			$this->addFlash('error', 'Упс... Что-то пошло не так! Попробуйте еще раз.');
		}
		
		return $this->redirectToRoute('homepage');
	}
	
	/**
     * @Route("/unsubscribe", name="unsubscribe")
     */
    public function unsubscribeAction(Request $request)
    {
		return $this->redirectToRoute('homepage');
	}
}