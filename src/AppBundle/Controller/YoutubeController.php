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
       // $this->get('youtube_functions')->setAuthorization($token);

        $this->getUser()->getCredentials()->setYoutubeToken($token['access_token']);
        $this->getUser()->getCredentials()->setYoutubeExpireAt(new \DateTime($token['expires_in']));
        $this->getUser()->getCredentials()->setYoutubeRefreshToken($token['refresh_token']);

        $this->getDoctrine()->getManager()->persist($this->getUser());
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('homepage');

    }

    private function refreshToken(){

        $youtubeToken = $this->getUser();
        $youtubeRefreshToken = $this->getUser()->getCredentials()->getYoutubeRefreshToken();

        $refreshToken =  $this->get('youtube_functions')->getRefreshToken($youtubeToken,$youtubeRefreshToken);

        $this->getUser()->getCredentials()->setYoutubeToken($token['access_token']);
        $this->getUser()->getCredentials()->setYoutubeRefreshToken($token['refresh_token']);


        $this->getDoctrine()->getManager()->persist($this->getUser());
        $this->getDoctrine()->getManager()->flush();
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

    /**
     * @param Request $request
     * @param $title
     *
     * @Route("/youtube/createplaylist", name="create_playlist")
     *
     */
    public function createPlaylistAction(Request $request,$title=null)
    {
        $title = $request->request->get('create_playlist');

        if($title != ''){

            $token = $this->getUser()->getCredentials()->getYoutubeToken();
            $createPlaylist = $this->get('youtube_functions')->createPlaylist($token, $title);
        }else{
            $createPlaylist = false;
        }

        return $this->render(':youtube:createPlaylist.html.twig', [
            'createPlaylist' => $createPlaylist
        ]);

    }
  
    /**
     * @return Response
     */
    public function getPlaylistAction(Request $request)
    {
            $playlists = $this->get('youtube_functions')->getPlaylist($this->getUser()->getCredentials()->getYoutubeToken());

            return $this->render(':default:dashboardNumber.html.twig', [
                'playlists' => $playlists
            ]);
    }
}

