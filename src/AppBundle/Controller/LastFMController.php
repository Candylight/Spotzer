<?php
/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:18
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Search;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LastFMController
 * @package AppBundle\Controller
 * @Route("lastFM")
 */
class LastFMController extends Controller
{
    /**
     * @Route("/search", name="lastFMSearch")
     *
     * @param string $keyword
     *
     * @return Response
     */
    public function getLastFMInfosAction($keyword)
    {
        $search = $this->getSearch($keyword);

        return $this->render('/search/searchLastFM.html.twig', [
            'search' => $search,
            'keyword' => $keyword
        ]);
    }

    /**
     * Check in DB to most searched artist
     * @return Response
     */
    public function homeInfosPanelAction()
    {
        $search = $this->getMostResearched();

        return $this->render('search/homePanel.html.twig',array(
            "search" => $search
        ));
    }

    /**
     * get searched artist top albums
     *
     * @return Response
     */
    public function getTopAlbumsAction($keyword)
    {
        $albums = json_decode($this->get('lastfm_functions')->searchTopAlbums($keyword),true);

        if(isset($albums['error']) || !is_array($albums) || count($albums) == 0 || !is_array($albums["topalbums"]["album"]) || count($albums["topalbums"]["album"]) == 0)
        {
            $albums = array();
        }
        else
        {
            $albums = $albums["topalbums"]["album"];
        }

        return $this->render('search/searchAlbums.html.twig',array(
            "albums" => $albums
        ));
    }

    /**
     * get artist tags
     *
     * @return Response
     */
    public function getTagsAction($keyword)
    {
        $tags = json_decode($this->get('lastfm_functions')->searchTags($keyword),true);
        $tags = $tags['toptags']['tag'];

        if(count($tags) > 10)
        {
            $tags = array_slice($tags, 0, 10);
        }

        return $this->render('search/searchTags.html.twig',array(
            "tags" => $tags
        ));
    }

    /**
     * Check if keyword exist in database and request lastFM otherwise
     * @param string $keyword
     *
     * @return mixed
     */
    private function getSearch($keyword)
    {
        if($keyword != "")
        {
            $search = $this->getDoctrine()->getRepository('AppBundle:Search')->findOneBy(array('searchText' => $keyword));
            if(!is_object($search))
            {
                $searchResult = $this->get('lastfm_functions')->getContent($keyword);

                if(is_array($searchResult))
                {
                    $search = new Search($keyword,$searchResult["title"],$searchResult["description"],$searchResult["image"],$searchResult["link"],1);
                }
                else
                {
                    $search = false;
                }
            }
            else
            {
                $search->setCount($search->getCount() + 1);
            }

            if(is_object($search))
            {
                $this->getDoctrine()->getManager()->persist($search);
                $this->getDoctrine()->getManager()->flush();
            }
        }
        else
        {
            $search = false;
        }

        return $search;
    }

    private function getMostResearched()
    {
        $topTen = $this->getDoctrine()->getRepository('AppBundle:Search')->findBy(array(),array("count"=>"DESC"),10);

        if(is_array($topTen) && count($topTen) > 0)
        {
            return $topTen[mt_rand(0, count($topTen) - 1)];
        }

        return false;
    }
}