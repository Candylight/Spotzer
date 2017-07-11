<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
        if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials()))
        {
            $playlists = $this->get('deezer_functions')->getPlaylist($this->getUser()->getCredentials()->getDeezerToken())->data;
        }
        return $this->render('deezer/playlist.html.twig',array(
            'playlists' => $playlists
        ));
    }

    /**
     * @Route("/deezer/playlist/create", name="deezer_create_playlist")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function createPlaylistAction(Request $request)
    {
        if($request->isMethod('POST'))
        {
            if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials()))
            {
                $this->get('deezer_functions')->createPlaylist($this->getUser()->getCredentials()->getDeezerToken(),$request->get('name'));
            }
            return $this->redirectToRoute('dashboard_deezer');
        }

        return $this->render('deezer/createPlaylist.html.twig');
    }

    /**
     * @Route("/deezer/getsongsplaylist", name="deezer_get_songs_playlist")
     */
    public function getTracksFromPlaylistAction(Request $request)
    {
        $deezerItems = array();

        if($request->isXmlHttpRequest()){
            $playlistId = $request->query->get('playlistid');
            if ($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
                $deezerItems = $this->get('deezer_functions')->getPlaylistById($this->getUser()->getCredentials()->getDeezerToken(), $playlistId)->tracks->data;
            }
        }

        return $this->render('dashboard/ajax/songsFromAlbumsDeezer.html.twig', [
            'songs' => $deezerItems
        ]);
    }


    /**
     * @Route("/deezer/addSongToPlaylist", name="deezer_add_track_to_playlist")
     */
    public function addSongToPlaylistAction(Request $request)
    {
        $playlistId = $request->get('playlistId', null);
        $trackId = $request->get('trackId', null);

        if($playlistId != null && $trackId != null)
        {
            if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials()))
            {
                $this->get('deezer_functions')->addItemToPlaylist($this->getUser()->getCredentials()->getDeezerToken(), $playlistId, $trackId);
            }
        }

        return new Response();
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