<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class
 *
 * @author                 Benjamin Hil <benjamin.hil@dnd.fr>
 * @copyright              Copyright (c) 2017 Agence Dn'D
 * @license                http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link                   http://www.dnd.fr/
 */
class DeezerController extends Controller
{
    /**
     * @Route("/deezer/login", name="deezer_login")
     *
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {

        return $this->redirect($this->get('deezer_functions')->getAuthorizationUrl());
    }

    /**
     * @Route("/deezer/callback", name="deezer_callback")
     *
     *
     * @return RedirectResponse
     */
    public function callbackAction()
    {
        $token = $this->get('deezer_functions')->getToken($_GET['code']);

        $this->getUser()->getCredentials()->setDeezerToken($token['token']);

        $date = new\DateTime();
        $date->setTimestamp($token['expirationDate']);
        $this->getUser()->getCredentials()->setDeezerExpireAt($date);

        if(!$this->getUser()->getSpotifyPrefered() && !$this->getUser()->getDeezerPrefered())
        {
            $this->getUser()->setDeezerPrefered(true);
        }

        $this->getDoctrine()->getManager()->persist($this->getUser());
        $this->getDoctrine()->getManager()->flush();


        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/deezer/result", name="deezer_search_result")
     *
     * @param string $keyword
     *
     * @return Response
     */
    public function getTopSongAction($keyword)
    {
        $musics = array();

        if ($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials()))
        {
            $musics = $this->get('deezer_functions')->getArtistTopTracks($keyword);
            if($musics > 10)
            {
                $musics = array_slice($musics,0 , 11);
            }
        }

        return $this->render('search/searchDeezer.html.twig', [
            'musics' => $musics
        ]);
    }

    public function getMySavedAlbumsAction()
    {
        $albums = array();
        if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials()))
        {
            $acessToken = $this->getUser()->getCredentials()->getDeezerToken();
            $albums = $this->get('deezer_functions')->getMySavedAlbums($acessToken)->data;
        }
        return $this->render('deezer/mySavedAlbums.html.twig', [
            'albums' => $albums
        ]);
    }

    public function getMyFlowAction()
    {
        $flow = array();
        if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $acessToken = $this->getUser()->getCredentials()->getDeezerToken();
            $flow = $this->get('deezer_functions')->getMyFlow($acessToken)->data;
        }
        return $this->render('deezer/flow.html.twig', [
            'flow' => $flow
        ]);
    }

    public function getMyTopArtistsAction()
    {
        $topArtist = array();
        if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $acessToken = $this->getUser()->getCredentials()->getDeezerToken();
            $topArtist = $this->get('deezer_functions')->getArtists($acessToken)->data;
        }
        return $this->render('deezer/myTopArtists.html.twig',[
            'topArtists' => $topArtist
        ]);
    }

    public function getMyFollowedAction()
    {
        $myFollowed = array();
        if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $acessToken = $this->getUser()->getCredentials()->getDeezerToken();
            $myFollowed = $this->get('deezer_functions')->getFollowings($acessToken)->data;
        }
        return $this->render('deezer/myFollowed.html.twig',[
            'myFollowed' => $myFollowed
        ]);
    }

    public function getRecommendationsAction()
    {
        $recommendations =  array();
        if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $acessToken = $this->getUser()->getCredentials()->getDeezerToken();
            $recommendations = $this->get('deezer_functions')->getTracksRecommendations($acessToken)->data;
        }
        return $this->render('deezer/recommendations.html.twig',[
            'recommendations' => $recommendations
        ]);
    }

    public function getPlaylistsAction()
    {
        $playlists = array();
        if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $playlists = $this->get('deezer_functions')->getPlaylist($this->getUser()->getCredentials()->getDeezerToken())->data;
        }
        return $this->render('transfer/ajax/playlist/deezerPlaylists.html.twig', [
            'deezerPlaylists' => $playlists,
        ]);
    }

    /**
     * @Route("/deezer/logout", name="deezer_logout")
     * @return RedirectResponse
     */
    public function logout()
    {
        /** @var User $user */
        $user = $this->getUser();

        $user->getCredentials()->setDeezerToken(null);
        $user->getCredentials()->setDeezerExpireAt(null);

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('account');
    }
}