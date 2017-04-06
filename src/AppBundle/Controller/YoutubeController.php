<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class YoutubeController extends Controller
{

    /**
     * @Route("/youtube/login", name="youtube_login")
     *
     *
     *
     */
    public function indexAction()
    {
        $redirectUrl = $this->generateUrl('youtube_callback',array(),UrlGeneratorInterface::ABSOLUTE_URL);

       return $this->redirect($this->get('youtube_functions')->getAuthorizationUrl($redirectUrl));



    }

    /**
     * @Route("/youtube/callback", name="youtube_callback")
     *
     *
     *
     */
    public function callbackAction()
    {
        /* traitement retour connexion youtube*/

        $session = new Session();
        $session->start();

        $token = $this->get('youtube_functions')->getToken($_GET['code']);

        $session->set('YOUTUBE_TOKEN',$token);

        return $this->redirectToRoute('homepage');

    }




}

