<?php
/**
 * Class
 *
 * @author                 Benjamin Hil <benjamin.hil@dnd.fr>
 * @copyright              Copyright (c) 2017 Agence Dn'D
 * @license                http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link                   http://www.dnd.fr/
 */


namespace AppBundle\Controller;

use AppBundle\Entity\Credentials;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SpotifyController extends Controller
{
    /**
     * @Route("/spotify/login", name="spotify_login")
     *
     *
     * @return httpResponse
     */
    public function indexAction()
    {

        return $this->redirect($this->get('spotify_functions')->getAuthorizationUrl());
    }

    /**
     * @Route("/spotify/callback", name="spotify_callback")
     *
     *
     * @return httpResponse
     */
    public function callbackAction()
    {
        $token = $this->get('spotify_functions')->getToken($_GET['code']);

        $this->getUser()->getCredentials()->setSpotifyToken($token['token']);

        $date = new\DateTime();
        $date->setTimestamp($token['expirationDate']);
        $this->getUser()->getCredentials()->setSpotifyExpireAt($date);

        if(!$this->getUser()->getSpotifyPrefered() && !$this->getUser()->getDeezerPrefered())
        {
            $this->getUser()->setSpotifyPrefered(true);
        }

        $this->getDoctrine()->getManager()->persist($this->getUser());
        $this->getDoctrine()->getManager()->flush();


        return $this->redirectToRoute('account');
    }

    public function getMySavedAlbumsAction()
    {
        $albums = array();
        if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials()))
        {
            $acessToken = $this->getUser()->getCredentials()->getSpotifyToken();
            $albums = $this->get('spotify_functions')->getMySavedAlbums($acessToken);
        }
        return $this->render('spotify/mySavedAlbums.html.twig', [
                'albums' => $albums
        ]);
    }

    public function getMyTopTracksAction()
    {
        $topTracks = array();
        if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $acessToken = $this->getUser()->getCredentials()->getSpotifyToken();
            $topTracks = $this->get('spotify_functions')->getMyTopTracks($acessToken);
        }
        return $this->render('spotify/myTopTracks.html.twig', [
            'topTracks' => $topTracks
        ]);
    }

    public function getMyTopArtistsAction()
    {
        $topArtists = array();
        if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $acessToken = $this->getUser()->getCredentials()->getSpotifyToken();
            $topArtists = $this->get('spotify_functions')->getMyTopArtists($acessToken);
        }
        return $this->render('spotify/myTopArtists.html.twig', [
            'topArtists' => $topArtists
        ]);
    }

    public function getMyFollowedAction()
    {
        $myFollowed = array();
        if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $acessToken = $this->getUser()->getCredentials()->getSpotifyToken();
            $myFollowed = $this->get('spotify_functions')->getMyFollowed($acessToken);
        }
        return $this->render('spotify/myFollowed.html.twig',[
            'myFollowed' => $myFollowed
        ]);
    }

    public function getRecommendationsAction()
    {
        $recommendations =  array();
        if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $acessToken = $this->getUser()->getCredentials()->getSpotifyToken();
            $recommendations = $this->get('spotify_functions')->getRecommendations($acessToken);
        }
        return $this->render('spotify/recommendations.html.twig',[
            'recommendations' => $recommendations
        ]);
    }

    public function getPlaylistsAction()
    {
        $spotifyPlaylists = array();
        if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
            $spotifyPlaylists = $this->get('spotify_functions')->getUserPlaylist($this->getUser()->getCredentials()->getSpotifyToken())->items;
        }
        return $this->render('transfer/ajax/playlist/spotifyPlaylists.html.twig', [
            'spotifyPlaylists' => $spotifyPlaylists,
        ]);
    }

    /**
     * @Route("spotify/getsongsplaylist", name="spotify_get_songs_playlist")
     */
    public function getTracksFromPlaylistAction(Request $request)
    {
        if($request->isXmlHttpRequest()){
            $playlistId = $request->query->get('playlistid');
            if ($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials())) {
                $spotifyItems = $this->get('spotify_functions')->getPlaylistItem($this->getUser()->getCredentials()->getSpotifyToken(), $playlistId)->items;
            }
            else{
                $spotifyItems = "null";
            }

            return $this->render('dashboard/ajax/songsFromAlbumsSpotify.html.twig', [
                'songs' => $spotifyItems
            ]);
        }
    }

    /**
     * @param string $keyword
     *
     * @Route("spotify/result", name="spotify_search_result")
     *
     * @return Response
     */
    public function getSpotifySongAction($keyword)
    {
        $musics = array();
        if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials()))
        {
            $acessToken = $this->getUser()->getCredentials()->getSpotifyToken();
            $topTracks = $this->get('spotify_functions')->getArtistTopTracks($keyword, $acessToken);
            $musics = [];
            foreach ($topTracks as $topTrack){
                $musics[] = $topTrack;
            }
        }
        
        return $this->render('search/searchSpotify.html.twig', [
            'musics' => $musics
        ]);
    }

    /**
     * @Route("/spotify/playlist/create", name="spotify_create_playlist")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function createPlaylistAction(Request $request)
    {
        if($request->isMethod('POST'))
        {
            if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials()))
            {
                $this->get('spotify_functions')->createPlaylist($this->getUser()->getCredentials()->getSpotifyToken(),$request->get('name'));
            }
            return $this->redirectToRoute('dashboard_spotify');
        }

        return $this->render('spotify/createPlaylist.html.twig');
    }

    /**
     * @Route("/spotify/logout", name="spotify_logout")
     */
    public function logout()
    {
        /** @var Credentials $credentials */
        $credentials = $this->getUser()->getCredentials();

        $credentials->setSpotifyToken(null);
        $credentials->setSpotifyRefreshToken(null);

        $this->getDoctrine()->getManager()->persist($credentials);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('account');
    }
}