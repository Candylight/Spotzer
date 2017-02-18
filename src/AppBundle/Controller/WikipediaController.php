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

class WikipediaController extends Controller
{
    /**
     * @Route("/wikipedia/search", name="wikiSearch")
     *
     * @param string $keyword
     *
     * @return Response
     */
    public function getWikiInfosAction($keyword)
    {
        $search = $this->getSearch($keyword);

        return $this->render('search/searchWikipedia.html.twig', [
            'search' => $search
        ]);
    }

    public function homeInfosPanelAction()
    {
        $search = $this->getMostResearched();

        return $this->render('search/homePanel.html.twig',array(
            "search" => $search
        ));
    }

    /**
     * Check if keyword exist in database and request wikipedia otherwise
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
                $searchResult = $this->get('wikipedia_functions')->getContent($keyword);

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
            $this->getDoctrine()->getManager()->persist($search);
            $this->getDoctrine()->getManager()->flush();
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

        if(is_array($topTen))
        {
            return $topTen[mt_rand(0, count($topTen) - 1)];
        }

        return false;
    }
}