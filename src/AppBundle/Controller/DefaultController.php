<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/welcome", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/", name="default")
     */
    public function defaultAction()
    {
        if($this->getUser() != null)
        {
            return $this->redirectToRoute('dashboard');
        }
        else
        {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/", name="default")
     */
    public function defaultAction()
    {
        if($this->getUser() != null)
        {
            return $this->redirectToRoute('dashboard');
        }
        else
        {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/", name="default")
     */
    public function defaultAction()
    {
        if($this->getUser() != null)
        {
            return $this->redirectToRoute('dashboard');
        }
        else
        {
            return $this->redirectToRoute('homepage');
        }
    }
}
