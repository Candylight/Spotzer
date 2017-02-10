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
        // replace this example code with whatever you need
        return $this->render('search/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }
}