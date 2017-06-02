<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Credentials;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class YoutubeController extends Controller
{

    /**
     * @Route("/youtube/login", name="youtube_login")
     *
     *
     * @return httpResponse
     */
    public function indexAction()
    {
       return $this->redirect($this->get('youtube_functions')->getAuthorizationUrl());
    }

    /**
     * @Route("/youtube/callback", name="youtube_callback")
     *
     *
     * @return httpResponse
     */
    public function callbackAction()
    {
        $token = $this->get('youtube_functions')->getToken($_GET['code']);

        $this->getUser()->getCredentials()->setYoutubeToken($token['access_token']);

       $this->getDoctrine()->getManager()->persist($this->getUser());
       $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('homepage');

    }

    /**
     * @param string $keyword
     *
     * @Route("youtube/result", name="youtube_search_result")
     */
    public function getYoutubeVideoAction($keyword)
    {

        if ($keyword != "") {

            $search = $this->get('youtube_functions')->search($keyword);
            $video = [];

            foreach ($search as $item) {
                $video[] = $this->get('youtube_functions')->video($item['id']['videoId']);
            }

        } else {

            $video = false;
        }

        return $this->render('search/searchMusicYoutube.html.twig', [
            'searches' => $video
        ]);
    }
}

