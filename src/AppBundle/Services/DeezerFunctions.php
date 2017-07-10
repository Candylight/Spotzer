<?php
/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:11
 */

namespace AppBundle\Services;


use AppBundle\Entity\Credentials;
use AppBundle\Library\DeezerAPI\DeezerAPI;
use AppBundle\Library\DeezerAPI\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class DeezerFunctions
{
    private $deezerClientId;
    private $deezerClientSecret;
    private $api;
    private $session;
    private $redirectUrl;
    private $router;

    /**
     * SpotifyFunctions constructor.
     * @param $deezerClientSecret
     * @param $deezerClientId
     */
    public function __construct($deezerClientId, $deezerClientSecret, $redirectUrl, $router)
    {
        $this->deezerClientId = $deezerClientId;
        $this->deezerClientSecret = $deezerClientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->router = $router;

        $this->session = new Session($this->deezerClientId, $this->deezerClientSecret);
        $this->api = new DeezerAPI();
        $this->session->setRedirectUri($this->router->generate($this->redirectUrl, array(), UrlGeneratorInterface::ABSOLUTE_URL ));
    }

    public function getAuthorizationUrl()
    {

        $options = ['perms' => ['basic_access', 'email', 'offline_access', 'manage_library', 'manage_community', 'delete_library', 'listening_history'],];

        return $this->session->getAuthorizeUrl($options);
    }

    public function getToken($code)
    {
        $this->session->requestAccessToken($code);

        return array(
            "token" => $this->session->getAccessToken(),
            "expirationDate" => $this->session->getTokenExpiration());
    }

    /**
     * @param Credentials $credentials
     *
     * @return bool
     */
    public function checkTokenValidity($credentials)
    {
        if($credentials->getDeezerToken() != null)
        {
            return true;
        }

        return false;
    }

    /**
     * @param string $artist
     */
    public function getArtistTopTracks($artist)
    {
        $id = $this->getArtistId($artist);
        $artist = $this->api->getArtist($id);
        $topSongs = $this->api->getTopSongs($artist->tracklist);
        return $topSongs->data;
    }

    /**
     * @param string $artist
     */
    public function getArtistId($artist)
    {
        $result = $this->api->search($artist,array(),"artist",false);

        return $result->data[0]->id;
    }

    public function createPlaylist($accessToken, $title)
    {

        $this->api->setAccessToken($accessToken);

        $options = [
            'title' => $title,
        ];

        return $this->api->createUserPlaylist($options, false);
    }

    public function searchBestResult($track)
    {
        return $this->api->search($track,['limit' => 1], 'track', false);
    }

    public function addItemToPlaylist($accessToken ,$playlistId, $trackId)
    {
          $this->api->setAccessToken($accessToken);

        return $this->api->addUserPlaylistTracks($playlistId, $trackId);
    }

    /**
     * @param string $album
     *
     * @return array
     */
    public function getAlbumSongs($album)
    {
        $albumId = $this->getAlbumId($album);
        $album = $this->api->getAlbum($albumId);
        $songs = $this->api->getAlbumSongs($album->tracklist);

        return $songs->data;
    }

    /**
     * @param string $album
     */
    public function getAlbumId($album)
    {
        $result = $this->api->search($album,array(),"album",false);

        return $result->data[0]->id;
    }

    public function getPlaylist($accessToken)
    {
        $this->api->setAccessToken($accessToken);


        return $this->api->getUserPlaylists();
    }

    public function getPlaylistById($accessToken, $playlistId)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->getPlaylist($playlistId);

    }

}