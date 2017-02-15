<?php
/**
 * Created by PhpStorm.
 * User: Thibault
 * Date: 10/02/2017
 * Time: 12:46
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends Controller
{

    /**
     * @Route("/search", name="search")
     */
    public function indexAction(Request $request)
    {
        if($request->get('keyword') != "")
        {
            list($title,$content,$link,$image) = $this->get('wikipedia_functions')->getContent($request->get('keyword'));
        }
        else
        {
            list($title,$content,$link,$image) = array("","","","");
        }

        // replace this example code with whatever you need
        return $this->render('search/index.html.twig', [
            'title' => $title,
            'content' => $content,
            'link' => $link,
            'image' => $image,
        ]);
    }
}