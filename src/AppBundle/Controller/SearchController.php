<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Library\Pagination\Pagination;

class SearchController extends Controller
{
    const NEWS_PER_PAGE = 10;
    
    /**
     * @Route("/search", name="search")
     */
    public function indexAction(Request $request)
    {
        $data = []; // will send to render
        $params = []; // will be used as db search params
        
        $newsRepo = $this->getDoctrine()->getRepository('AppBundle:News');
        $categoryRepo = $this->getDoctrine()->getRepository('AppBundle:Category');
        
        // find all categories and tags to fill in search control panel
        $data['categories'] = $categoryRepo->findAll();
        $data['tags'] = $this->explodeTagSets($newsRepo->findTags());
        
        // look for get params
        $data['getParams'] = null;
        if ($request->query->count() > 0) {
            if (!is_null($request->query->get('categories'))) {
                $params['category_set'] = explode('|', $request->query->get('categories'));
                $data['getParams'] .= "categories=" . $request->query->get('categories') . "&";
                $data['params']['category_set'] = explode('|', $request->query->get('categories'));
            }
            
            if (!is_null($request->query->get('tags'))) {
                $params['tag_set'] = explode('|', $request->query->get('tags'));
                $data['getParams'] .= "tags=" . $request->query->get('tags') . "&";
                $data['params']['tag_set'] = explode('|', $request->query->get('tags'));
            }
            
            // db contains dates in unix timestamps
            // strtotime: normal date -> unix timestamp
            if (!is_null($request->query->get('dfrom'))) {
                $params['dfrom'] = strtotime($request->query->get('dfrom'));
                $data['getParams'] .= "dfrom=" . $request->query->get('dfrom') . "&";
                $data['params']['dfrom'] = $request->query->get('dfrom');
            }
            
            if (!is_null($request->query->get('dto'))) {
                $params['dto'] = strtotime($request->query->get('dto'));
                $data['getParams'] .= "dto=" . $request->query->get('dto') . "&";
                $data['params']['dto'] = $request->query->get('dto');
            }
        }
        
        $page = $request->query->get('page');
        $page = is_null($page) ? 1 : $page;
        
        // find count of news matched to requested params
        $newsCount = $newsRepo->filterCount($params);
        
        // find all news matched to requested params
        $data['newsList'] = $newsRepo->filter(self::NEWS_PER_PAGE, ($page - 1) * self::NEWS_PER_PAGE, $params);
        // echo count($data['newsList']); die;
        $pagination = new Pagination([
            'itemsCount' => $newsCount,
            'itemsPerPage' => self::NEWS_PER_PAGE,
            'currentPage' => $page
        ]);
        
        $data['pagination'] = $pagination;
        $data['btnCount'] = count($pagination->buttons);
        
        return $this->render('search/index.html.twig', $data);
    }
    
    /**
     * @Route("/search/bar", name="searchbar")
     * 
     * Used for search bar to get list of similar tags
     * @return JsonResponse
     */
    public function barAction(Request $request)
    {
        // look for post params
        $param = $request->request->get('searchtag');
        
        if ($param) {
            $data = [];
            
            // get data from db according to search params
            // finds all possible tag sets
            $newsRepo = $this->getDoctrine()->getRepository('AppBundle:News');
            $tagSets = $newsRepo->findTags();
            
            // create an array with one tag per index
            $separatedTags = $this->explodeTagSets($tagSets);
            
            foreach ($separatedTags as $tag) {
                if (mb_stripos($tag, $param) > -1) {
                    $data[] = json_encode([
                        'name' => $tag,
                        'url' => $this->generateUrl('search', ['tags' => $tag])
                    ]);
                }
            }
            
            if (!empty($data)) {
                return new JsonResponse(['data' => $data], 200);
            }
        }
        return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
    }
    
    /**
     * Used to get tags array
     * 
     * @param array [0 => ['tagSet' => tag_set_string], ... ]
     * 
     * @return array [0 => tag0, 1 => tag1, ...]
     * 
     */
    public static function explodeTagSets($tagSetList)
    {
        array_walk($tagSetList, function(&$value, $key) {
            $value = $value['tagSet'];
        });
        
        return array_unique(explode(';', implode(';', $tagSetList)));
    }
}
