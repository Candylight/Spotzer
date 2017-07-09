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
    public function callbackAction(Request $request)
    {
        $session = $request->getSession();
        $token = $this->get('youtube_functions')->getToken($_GET['code']);
        $accessToken = $session->get('access_token');
        $accessToken = $session->set('access_token', $token['access_token']);

        $this->getUser()->getCredentials()->setYoutubeToken($token['access_token']);

        $date = new \DateTime();
        $date->add(new \DateInterval('PT' . $token['expires_in'] . 'S'));

        $this->getUser()->getCredentials()->setYoutubeExpireAt($date);
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

        $this->getUser()->getCredentials()->setYoutubeToken($youtubeToken);
        $this->getUser()->getCredentials()->setYoutubeRefreshToken($youtubeRefreshToken);

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
     * @return Response
     * @Route("/youtube/createplaylist", name="create_playlist")
     */
    public function createPlaylistAction(Request $request)
    {
        if ($this->checkConnexion()) {

            $title = $request->request->get('title');
            $desc = $request->request->get('description');
            $status = $request->request->get('status');

            if (($request->isMethod('POST')) && ($title != '') && ($desc != '')) {
                $createPlaylist = $this->get('youtube_functions')->createPlaylist($this->getUser()->getCredentials(), $title, $desc, $status);
            } else {
                $createPlaylist = false;
            }
        } else {
            $playlists = false;
        }
        return $this->render('youtube/createPlaylist.html.twig');

    }

    /**
     * @return Response
     */
    public function getPlaylistAction(Request $request)
    {
        if ($this->checkConnexion()) {
            $playlists = $this->get('youtube_functions')->getPlaylist($this->getUser()->getCredentials());
        } else {
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
        if ($this->checkConnexion()) {

            $playlists = $this->get('youtube_functions')->getPlaylist($this->getUser()->getCredentials());

            foreach ($playlists as $playlist) {
                $items[] = $this->get('youtube_functions')->getPlaylistItems($this->getUser()->getCredentials(), $playlist['id']);
            }
        } else {
            $items = false;
        }

        return $this->render('youtube/playlistItems.html.twig', [
            'items' => $items
        ]);
    }

    /**
     * @return Response
     */
    public function getRecommendationVideoAction()
    {
        if ($this->checkConnexion()) {

            $recomVideos[] = $this->get('youtube_functions')->getMostPopularVideo();
        } else {
            $recomVideos = false;
        }

        return $this->render('youtube/recommendationVideo.html.twig', [
            'recomVideos' => $recomVideos
        ]);

    }

    /**
     * @return Response
     */
    public function getVideoByLikeAction()
    {
        if ($this->checkConnexion()) {

            $videos[] = $this->get('youtube_functions')->getVideoByLike($this->getUser()->getCredentials());
        } else {
            $videos = false;
        }

        return $this->render('youtube/videoByLike.html.twig', [
            'videos' => $videos
        ]);
    }

    public function getMyChannelsAction()
    {
        if ($this->checkConnexion()) {

            $myChannels = $this->get('youtube_functions')->getSubscription($this->getUser()->getCredentials());
        } else {
            $myChannels = false;
        }

        return $this->render('youtube/myChannels.html.twig', [
            'myChannels' => $myChannels
        ]);
    }

    /**
     * @return string token
     */
    public function getToken()
    {
        return $this->getUser()->getCredentials()->getYoutubeToken();
    }


    private function checkConnexion()
    {
        if (!$this->get('youtube_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            return false;
        }

        return true;
    }

    /**
     * @Route("youtube/logout", name="youtube_logout")
     *
     */
    public function logout()
    {
        $user = $this->getUser();

        $user->getCredentials()->setYoutubeToken('');

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('account');
    }

}

