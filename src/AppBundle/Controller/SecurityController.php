<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\User;
use AppBundle\Entity\Subscriber;
use AppBundle\Form\RegistrationType;
use AppBundle\Form\LoginType;

class SecurityController extends Controller
{
    /**
     * @Route("/registration", name="registration")
     */
    public function registrationAction(Request $request) 
    {
        $data = [];
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
			if ($this->checkStringForSymbols($user->getUsername())) {
				$userPassword = $this->get('security.password_encoder')
					->encodePassword($user, $user->getPlainPassword());
				
				$user->setPassword($userPassword);
				
				$manager = $this->getDoctrine()->getManager();
				try {
					$userIsSub = $manager->getRepository('AppBundle:Subscriber')
						->findOneByEmail($user->getEmail());
					
					$user->setIsSub((bool)$userIsSub);
					
					$manager->persist($user);
					$manager->flush();
					// set flash msg about succed saving
					$this->addFlash('notice', 'Регистрация успешна!');
					return $this->redirectToRoute('homepage');
				} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
					// set flash msg about failed saving
					$this->addFlash('error', 'Имя или эл. адресс уже заняты!');
				}
			} else {
				$this->addFlash('error', 'Имя пользователя содержит недопустимые символы!');
			}
        }
		
        $data['form'] = $form->createView();
		$data['errors'] = $form->getErrors(true, false);
        
        return $this->render('security/form.registration.html.twig', $data);
    }
    
    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
    
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render('security/form.login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'utils' => $authenticationUtils
        ));
    }
	
	/**
     * @Route("/reset/mail", name="reset_mail")
     */
    public function resetMailAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')
				->isGranted('IS_AUTHENTICATED_FULLY')) {
			return $this->redirectToRoute('homepage');
		}
		
		// get plain pwd
		$newEmail = $request->request->get('newMail');
		$plainPassword = $request->request->get('plainPassword');
		$repeatedPlainPassword = $request->request
			->get('repeatedPlainPassword');
		
		if (!$newEmail
			|| !$plainPassword
			|| !$repeatedPlainPassword
			|| ($plainPassword !== $repeatedPlainPassword)) {
			return new JsonResponse([], 406);
		}
		
		// compare excepted pwd and user pwd & check email
		$user = $this->getUser();
		
		// checks user email
		if ($newEmail == $user->getEmail()) {
			return new JsonResponse([
				'data' => json_encode([
					'msg' => 'Вы указали Ваш текущий эл. адресс!'
				])],
				200
			);
		}
		
		$manager = $this->getDoctrine()->getManager();
				
		// checks encoded passwords matching
		if (!$this->get('security.password_encoder')
				->isPasswordValid($user, $plainPassword)) {
			return new JsonResponse([], 406);
		}
		
		$oldEmail = $user->getEmail();
		$user->setEmail($newEmail);
		// then checks is user subscribed
		$sub = $manager->getRepository('AppBundle:Subscriber')
			->findOneByEmail($oldEmail);
		if ($sub) { // if subscribed
			$sub->setEmail($newEmail); // sets new email for user's subscribtion record
		}
		try {
			$manager->flush();
		} catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
			return new JsonResponse([], 400);
		} catch (\Exception $e) {
			return new JsonResponse([], 500);
		}
		return new JsonResponse(
			['data' => json_encode(['msg' => 'Сохранено!'])],
			200
		);
	}
	
	/**
     * @Route("/reset/pwd", name="request_reset_pwd")
     */
    public function resetRequestAction(Request $request)
	{
		$email = $request->query->get('email');
		if (!$email) {
			return $this->render('security/form.mail.html.twig', [
				'message' => 'Укажите почту, которая зарегестрирована на Вашем аккаунте'
			]);
		}
		
		// work with db
		$manager = $this->getDoctrine()->getManager();
		$user = $manager->getRepository('AppBundle:User')
			->findOneByEmail($email);
		
		if (!$user) {
			return $this->render('security/form.mail.html.twig', [
				'message' => 'Аккаунта с указанным почтовым адрессом не существует'
			]);
		}
		
		// create and register reset key
		$resetKey = new \AppBundle\Entity\Resets();
		$resetKey->setUserId($user->getId());
		$resetKey->setDescription('Password reset key');
		$manager->persist($resetKey);
		try {
			$manager->flush();
		} catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
			return $this->render('security/form.mail.html.twig', [
				'message' => 'Упс... Что-то пошло не так. Попробуйте еще раз!'
			]);
		}
		
		// create a message
		$mailer = $this->get('mailer');
		$message = (new \Swift_Message('Смена пароля'))
			->setFrom([$this->getParameter('mailer_user') => 'News Portal Admin'])
			->setTo([$email => $user->getRealName()])
			->setBody(
				$this->renderView(
					'mail_templates/reset_pwd.html.twig',
					['key' => $resetKey->getResetKey()]
				),
				'text/html'
			)
		;

		// Send the message
		$result = $mailer->send($message);
		
		return $this->render('message_boxes/msg.html.twig', [
			'message' => 'Сообщение с деталями об смене пароля было отослано на Вашу почту'
		]);
	}
	
	/**
     * @Route("/set/pwd", name="reset_pwd")
     */
    public function resetPasswordAction(Request $request)
	{
		// get reset uniq key
		$resetKey = $request->query->get('key');
		if (!$resetKey) {
			if (!$request->request->get('formSubmit')) {
				$this->redirectToRoute('login');
			} else {
				$resetKey = $request->request->get('key');
			}
		}
		
		// checks reset key
		$manager = $this->getDoctrine()->getManager();
		$resetKey = $manager->getRepository('AppBundle:Resets')
			->findOneByResetKey($resetKey);
		
		if (!$resetKey) {
			$this->container->get('logger')->info('key');
			return $this->render('message_boxes/msg.html.twig', [
				'message' => 'Данная ссылка не действительна!'
			]);
		}
		
		// checks time
		$timeDiff = time() - $resetKey->getCreated();
		$keyCanExist = 2 * 60 * 60; // key existents limit
		if ($timeDiff > $keyCanExist) {
			$this->container->get('logger')->info('time');
			return $this->render('message_boxes/msg.html.twig', [
				'message' => 'Данная ссылка не действительна!'
			]);
		}
		
		// look for user with excepted reset key
		$user = $manager->getRepository('AppBundle:User')
			->find($resetKey->getUserId());
		
		if (!$user) {
			$this->container->get('logger')->info('user');
			return $this->render('message_boxes/msg.html.twig', [
				'message' => 'Данная ссылка не действительна!'
			]);
		}
		
		$formData = $request->request->all();
		// checks form submission and is form validate
		if (isset($formData['formSubmit'])) {
			if (!isset($formData['plainPassword'])
				|| !isset($formData['repeated_plainPassword'])) {
				
				$this->addFlash('notice', 'Не все поля заполнены!');
				return $this->render('security/form.password.html.twig', [
					'resetKey' => $resetKey->getResetKey(),
					'form_dump' => $formData
				]);
			}
			
			if ($formData['plainPassword'] != $formData['repeated_plainPassword']) {
				$this->addFlash('notice', 'Пароли не совпадают!');
				return $this->render('security/form.password.html.twig', [
					'resetKey' => $resetKey->getResetKey(),
					'form_dump' => $formData
				]);
			}
			
			$userPassword = $this->get('security.password_encoder')
				->encodePassword($user, $formData['plainPassword']);
			
			$user->setPassword($userPassword);
			
			try {
				$this->getDoctrine()->getManager()
					->flush();
				// set flash msg about succed saving
				$this->addFlash('notice', 'Пароль был успешно сменен!');
				return $this->redirectToRoute('homepage');
			} catch(\Exception $e) {
				// set flash msg about failed saving
				$this->addFlash('notice', 'Упс... Что-то пошло не так!');
			}
		}
		
		// return reset form
		return $this->render('security/form.password.html.twig', [
			'resetKey' => $resetKey->getResetKey(),
			'form_dump' => $formData
		]);
	}
	
	/**
	 * @Route("/set/role", name="set_role")
	 */
	public function roleAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')
				->isGranted('ROLE_ADMIN')) {
			return new JsonResponse([], 400);
		}
		
		$userId = $request->request->get('userid');
		$_role = $request->request->get('role');
		
		if (!$userId
			|| !$_role
			|| !is_numeric($userId)) {
			
			return new JsonResponse([], 400);
		}
		
		$role = "ROLE_{$_role}";
		$roleExists = false;
		
		// gets list of roles
		$roles = array_keys($this->getParameter('security.role_hierarchy.roles'));
		
		for ($i = 0, $len = sizeof($roles); $i < $len; $i++) {
			// checks role existents
			if ($roles[$i] === $role) {
				$roleExists = true;
				break;
			}
		}
		
		$manager = $this->getDoctrine()->getManager();
		$user = $manager->getRepository('AppBundle:User')
			->find($userId);
		
		if (!$roleExists
			|| !$user) {
			
			return new JsonResponse([], 400);
		}
		
		$user->setRoles($role);
		try{
			$manager->flush();
		} catch (\Exception $e) {
			return new JsonResponse([], 500);
		}
		
		return new JsonResponse([
			'data' => json_encode([
				'role' => $_role,
				'userid' => $userId
			])
		], 200);
	}

	/**
	 * @return boolean 
	 * 	true if $string doesnt contain any of $symbols
	 * 	false if contains
	 */
	public function checkStringForSymbols($str, $symbols = null)
	{
		if (!$str) {
			return false;
		}
		if (is_null($symbols)) {
			$symbols = [
				',', '.', '/', '\'', '"', '[', ']', '{', '}',
				'<', '>', '!', '#', '№', ':', ';', '%', '^',
				'?', '*', '(', ')'
			];
		}
		
		foreach ($symbols as $sym) {
			if (false === strpos($str, $sym)) {
				return false;
			}
		}
	}
}
