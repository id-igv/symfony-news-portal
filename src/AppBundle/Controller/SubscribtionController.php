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
		
		if (!$email) {
				
			$this->addFlash('error', 'Неуказана почта!');
			return $this->redirectToRoute('homepage');
		}
		
		$sub = new Subscriber();
		$sub->setEmail($email);

		$categories = $this->getDoctrine()->getRepository('AppBundle:Category')
			->findAll();
		$catsForSubscribtion = [];
		foreach ($categories as $cat) {
			$catsForSubscribtion[] = $cat->getId();
		}
		$sub->setCategoryList(implode(';', $catsForSubscribtion));
		
		try {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($sub);
			$manager->flush();
			$this->addFlash('notice', 'Успешно подписаны! Управлять списком новостных категорий, на которые Вы подписались, могут только зарегестрированные пользователи.');
		} catch (\Exception $e) {
			$this->addFlash('error', 'Упс... Что-то пошло не так! Попробуйте еще раз. Возможно данный эл. адресс уже подписан.');
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
