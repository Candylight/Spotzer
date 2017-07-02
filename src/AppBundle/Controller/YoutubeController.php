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
     * @Route("/youtube/callback", name="youtube_callback")
     *
     *
     * @return httpResponse
     */
    public function callbackAction(Request $request)
    {
        $session = $request->getSession();
        $token = $this->get('youtube_functions')->getToken($_GET['code']);
        $accessToken = $session->get('access_token');
        $accessToken = $session->set('access_token', $token['access_token']);

        $this->getUser()->getCredentials()->setYoutubeToken($token['access_token']);
        $this->getUser()->getCredentials()->setYoutubeExpireAt(new \DateTime($token['expires_in']));
        $this->getUser()->getCredentials()->setYoutubeRefreshToken($token['refresh_token']);

        $this->getDoctrine()->getManager()->persist($this->getUser());
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('dashboard');

    }

    private function refreshToken()
    {
        $youtubeToken = $this->getUser();
        $youtubeRefreshToken = $this->getUser()->getCredentials()->getYoutubeRefreshToken();

        $refreshToken = $this->get('youtube_functions')->getRefreshToken($youtubeToken, $youtubeRefreshToken);

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

        return $this->render('search/searchYoutube.html.twig', [
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
    public function createPlaylistAction(Request $request, $title = null)
    {
        $title = $request->request->get('create_playlist');
        if ($title != '') {
            $token = $this->getUser()->getCredentials()->getYoutubeToken();
            $createPlaylist = $this->get('youtube_functions')->createPlaylist($token, $title);
        } else {
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
        $token = $this->getToken();
        $access =  $this->get('youtube_functions')->fetchAccessTokenWithAuthCode($token);

        if($access == true){
            $playlists = $this->get('youtube_functions')->getPlaylist($this->getUser()->getCredentials()->getYoutubeToken());
        } else{
            $playlists = false;
        }

        return $this->render('dashboard/indexNumber.html.twig', [
            'playlists' => $playlists
        ]);
    }

    /**
     * @return Response
     */
    public function getPlaylistItemsAction()
    {
        $token = $this->getToken();
        $playlists = $this->get('youtube_functions')->getPlaylist($token);
        foreach ($playlists as $playlist) {
            $items = $this->get('youtube_functions')->getPlaylistItems($token, $playlist['id']);
        }

        return $this->render('dashboard/indexNumber.html.twig', [
            'items' => $items
        ]);
    }

    /**
     * @return string token
     */
    public function getToken()
    {
        return $this->getUser()->getCredentials()->getYoutubeToken();
    }
}

