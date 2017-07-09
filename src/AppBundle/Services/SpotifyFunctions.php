<?php

/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:10
 */

namespace AppBundle\Services;

use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SpotifyFunctions
{
    private $spotifyClientId;
    private $spotifyClientSecret;
    private $api;
    private $session;
    private $redirectUrl;
    private $router;

    /**
     * SpotifyFunctions constructor.
     * @param $spotifyClientSecret
     * @param $spotifyClientId
     */
    public function __construct($spotifyClientId, $spotifyClientSecret, $redirectUrl, $router)
    {
        $this->spotifyClientId = $spotifyClientId;
        $this->spotifyClientSecret = $spotifyClientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->router = $router;

        $this->session = new \SpotifyWebAPI\Session($this->spotifyClientId, $this->spotifyClientSecret);
        $this->api = new SpotifyWebAPI();
        $this->session->setRedirectUri($this->router->generate($this->redirectUrl, array(), UrlGeneratorInterface::ABSOLUTE_URL ));
    }

    public function getAuthorizationUrl()
    {

        $options = ['scope' => [
            'playlist-read-private',
            'playlist-read-collaborative',
            'playlist-modify-public',
            'playlist-modify-private',
            'streaming',
            'user-follow-modify',
            'user-follow-read',
            'user-library-read',
            'user-library-modify',
            'user-read-private',
            'user-read-birthdate',
            'user-read-email',
            'user-top-read',
        ],];

        return $this->session->getAuthorizeUrl($options);
    }

    public function getToken($code)
    {
        $this->session->requestAccessToken($code);

        return $this->session->getAccessToken();

    }

    public function getUserPlaylist($accessToken)
    {
        $this->api->setAccessToken($accessToken);


        return $this->api->getMyPlaylists();
    }

    public function getArtistId($keyword, $accessToken)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->search($keyword, 'artist')->artists->items[0]->id;
    }

    public function getArtistTopTracks($keyword, $accessToken)
    {
        $options = ['country' => 'FR'];

        return $this->api->getArtistTopTracks($this->getArtistId($keyword, $accessToken), $options);
    }

    public function createPlaylist($accessToken, $title)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->createUserPlaylist($this->getCurrentUserId($accessToken), ['name' => $title, 'public' => false]);
    }

    public function getCurrentUserId($accessToken)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->me()->id;
    }

    public function getPlaylistItem($accessToken, $playlistID)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->getUserPlaylistTracks($this->getCurrentUserId($accessToken), $playlistID);

    }

    public function getPlaylistByID($accessToken, $playlistID)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->getUserPlaylist($this->getCurrentUserId($accessToken), $playlistID);

    }

    public function searchBestResult($accessToken, $keyword)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->search($keyword, 'track', ['limit' => 1]);

    }

    public function addItemToPlaylist($accessToken, $playlistId, $trackId)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->addUserPlaylistTracks($this->getCurrentUserId($accessToken), $playlistId, [$trackId]);
    }

}