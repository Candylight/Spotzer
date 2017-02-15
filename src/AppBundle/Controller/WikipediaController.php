<?php
/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:18
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WikipediaController extends Controller
{
    /**
     * @Route("/wikipedia/search/{keyword}", name="wikiSearch")
     */
    public function searchAction($keyword)
    {
        list($title,$content,$link,$image) = $this->get('wikipedia_functions')->getContent($keyword);

        return $this->render('wikipedia/index.html.twig',array(
            "title" => $title,
            "content" => $content,
            "link" => $link,
            "image" => $image,
        ));
    }
}