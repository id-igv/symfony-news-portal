<?php

namespace AppBundle\Controller\API;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Library\DeferredList;
use AppBundle\Entity\Subscriber;

class ProfileController extends Controller
{
    /**
     * @Route("/api/profile", name="api_profile")
     */
    public function indexAction(Request $request)
	{
		return new JsonResponse(['data' => $data], 200);
	}
	
    /**
     * @Route("/api/profile/edit/realname", name="api_profile_edit")
     */
    public function editAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse([], 400);
		}
		
		$realName = $request->request->get('realname');
		
		if (!$realName) {
			return new JsonResponse([], 400);
		}
		
		$realName = htmlspecialchars(
			trim($realName),
			ENT_COMPAT
		);
		if (mb_strlen($realName) > 64) {
			return new JsonResponse([], 400);
		}
		
		$this->getUser()->setRealName($realName);
		try {
			$this->getDoctrine()->getManager()->flush();
		} catch (\Exception $e) {
			return new JsonResponse([], 500);
		}
		
		return new JsonResponse([
			'data' => json_encode(['msg' => 'Сохранено!'])
		], 200);
	}
	
	/**
     * @Route("/api/profile/bookmark/edit", name="api_bookmark_edit")
     */
    public function editBookmarkAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse([
				'data' => json_encode(['errorMsg' => 'Войдите на сайт!'])
			], 400);
		}
		
		$newsId = $request->request->get('id');
		
		if ($newsId == null) {
			return new JsonResponse([
				'data' => json_encode(['errorMsg' => 'Неверные данные!'])
			], 400);
		}
		
		$manager = $this->getDoctrine()->getManager();
		
		if ($manager->getRepository('AppBundle:News')->find($newsId) == null) {
			return new JsonResponse([
				'data' => json_encode([
					'errorMsg' => 'Статьи не надено. Обновите страницу статьи!'
				])
			], 400); 
		}
		
		$message = '';
		$deferredList = new DeferredList($this->getUser()->getDeferred());
		
		if (!$deferredList->isFull()) {
			$key = $deferredList->exist($newsId);
			
			if ($key < 0) {
				$deferredList->add($newsId);
				$message = 'Сохранено';
			} else {
				$deferredList->removeByKey($key);
				$message = 'Запомнить';
			}
			
			$this->getUser()->setDeferred($deferredList->toArray());
			
			try {
				$manager->flush();
			} catch(\Doctrine\ORM\ORMException $e) {
				return new JsonResponse([
					'data' => json_encode([
						'errorMsg' => 'Не удалось обновить профиль пользователя. Попробуйте позже!'
					])
				], 400);
			}
		} else {
			return new JsonResponse([
				'data' => json_encode([
					'erroMsg' => 'Список отложенных статей полный!'
				])
			], 400);
		}
		
		return new JsonResponse([
			'data' => json_encode([
				'msg' => $message
			])
		], 200);
	}

	/**
     * @Route("/api/profile/bookmarks/clear", name="api_bookmarks_clear")
     */
    public function clearBookmarksAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')
				->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse([], 400);
		}
		
		$this->getUser()->setDeferred([]);
		try {
			$this->getDoctrine()->getManager()
				->flush();
		} catch (\Exception $e) {
			return new JsonResponse([], 500);
		}
		
		return new JsonResponse([
			'data' => json_encode([
				'msg' => 'Очищено!'
			])
		], 200);
	}
	
	/**
     * @Route("/api/profile/history/clear", name="api_news_history_clear")
     */
    public function clearNewsHistoryAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')
				->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse([], 400);
		}
		
		$this->getUser()->setViewed([]);
		try {
			$this->getDoctrine()->getManager()
				->flush();
		} catch (\Exception $e) {
			return new JsonResponse([], 500);
		}
		
		return new JsonResponse([
			'data' => json_encode([
				'msg' => 'Очищено!'
			])
		], 200);
	}
	
    /**
     * Avatar is uploaded from URL
     * @Route("/api/avatar/linkupload", name="api_av_upload_link")
     */
    public function avUploadLinkAction(Request $request)
	{
		$logger = $this->container->get('logger');
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse([], 400);
		}
		
		$imagePath = '';
		$imageNamePrefix = 'avatar_id';
		
		$url = $request->request->get('url');
		if (!$url) {
			return new JsonResponse([], 400);
		}
		
		if (false === ($imagePath = $this->get('app.uploader')
				->uploadFromUrl($url))) {
				return new JsonResponse([], 500);
		}
		
		$crntImagePth = $this->getUser()->getAvatar();
		if ($crntImagePth != '' && false === strpos($crntImagePth, $this->getParameter('standart_av_dir'))) {
			// deleting old image
			$fullCrntPath = realpath($crntImagePth);
			if ($fullCrntPath === false && $fullCrntPath !== '') {
				return new JsonResponse([], 500);
			}
			
			file_put_contents($fullCrntPath, ''); // clearing file content
			if(!unlink($fullCrntPath)) {
				return new JsonResponse([], 500);
			}
			// end of deleting
		}
		$this->getUser()->setAvatar($imagePath);
		try {
			$this->getDoctrine()->getManager()->flush();
		} catch (\Doctrine\ORM\ORMException $e) {
			return new JsonResponse([], 500);
		}
		
		return new JsonResponse([
			'data' => json_encode([
				'imgUrl' => '/' . $imagePath
			])
		], 200);
	}
	
	/**
     * Avatar is uploaded from users HD
     * @Route("/api/avatar/upload", name="api_av_upload_local")
     */
    public function avUploadLocalAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse([], 400);
		}
		
		$imagePath = '';
		$imageNamePrefix = 'avatar_id';
		
		$file = $request->files->get('profile_av');
		
		if (!$file) {
			return new JsonResponse([], 400);
		}
		
		$imagePath = $this->get('app.uploader')
			->saveUploaded($file);
		
		$crntImagePth = $this->getUser()->getAvatar();
		if ($crntImagePth != '' && false === strpos($crntImagePth, $this->getParameter('standart_av_dir'))) {
			// deleting old image
			$fullCrntPath = realpath($crntImagePth);
			if ($fullCrntPath === false) {
				return new JsonResponse([], 500);
			}
			
			file_put_contents($fullCrntPath, ''); // clearing file content
			if(!unlink($fullCrntPath)) {
				return new JsonResponse([], 500);
			}
			// end of deleting
		}
		$this->getUser()->setAvatar($imagePath);
		try {
			$this->getDoctrine()->getManager()->flush();
		} catch (\Doctrine\ORM\ORMException $e) {
			return new JsonResponse([], 500);
		}
		
		return new JsonResponse([
			'data' => json_encode([
				'imgUrl' => '/' . $imagePath
			])
		], 200);
	}
	
	/**
     * @Route("/api/subscribe", name="api_subscribe")
     */
    public function subscribeAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse([], 400);
		}
		
		$categories = $request->request->get('categoryList');
		
		if (!$categories) {
			return new JsonResponse([], 400);
		}
		
		$user = $this->getUser();
		$manager = $this->getDoctrine()->getManager();
		
		$sub = $this->getDoctrine()->getRepository('AppBundle:Subscriber')
			->findOneByEmail(
				$user->getEmail()
			);
		
		$connection = $manager->getConnection();
		$connection->beginTransaction();
		
		try {
			if ($sub) {
				$sub->setCategoryList(implode(';', $categories));
			} else {
				$sub = new Subscriber();
				$sub->setEmail($user->getEmail());
				$sub->setCategoryList(implode(';', $categories));
				$manager->persist($sub);
			}
			$manager->flush();
			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollBack();
		}
		
		return new JsonResponse([], 200);
	}
	
	/**
     * @Route("/api/unsubscribe", name="api_unsubscribe")
     */
    public function unsubscribeAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse([], 400);
		}
		
		$user = $this->getUser();
		$manager = $this->getDoctrine()->getManager();
		$sub = $manager->getRepository('AppBundle:Subscriber')
			->findOneByEmail($user->getEmail());
		
		if (!$sub) {
			return new JsonResponse([], 400);
		}
		try {
			$manager->remove($sub);
			$manager->flush();
		} catch(\Exception $e) {
			return new JsonResponse([], 500);
		}
		
		return new JsonResponse([], 200);
	}
}
?>
