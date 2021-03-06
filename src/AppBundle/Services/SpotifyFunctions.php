<?php

/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:10
 */

namespace AppBundle\Services;

use AppBundle\Entity\Credentials;
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

        return array(
            "token" => $this->session->getAccessToken(),
            "expirationDate" => $this->session->getTokenExpiration());

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

    /**
     * @param Credentials $credentials
     *
     * @return bool
     */
    public function checkTokenValidity($credentials)
    {
        if($credentials->getSpotifyToken() != null)
        {
            if($credentials->getSpotifyExpireAt() > new \DateTime())
                return true;
        }

        return false;
    }

    /**
     * @param $album
     *
     * @return array
     */
    public function getAlbumSongs($accessToken, $album)
    {
        $this->api->setAccessToken($accessToken);
        $albumId = $this->getAlbumId($album);
        $songs = $this->api->getAlbumTracks($albumId);

        return $songs->items;
    }

    /**
     * @param $album
     */
    public function getAlbumId($album)
    {
        $result = $this->api->search($album, 'album', ['limit' => 1]);
        return $result->albums->items[0]->id;
    }

    public function getMySavedAlbums($accessToken)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->getMySavedAlbums();
    }

    public function getMyTopTracks($accessToken)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->getMyTop('tracks');
    }

    public function getMyTopArtists($accessToken)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->getMyTop('artists');
    }

    public function getMyFollowed($accessToken)
    {
        $this->api->setAccessToken($accessToken);

        return $this->api->getUserFollowedArtists();
    }

    public function getRecommendations($accessToken)
    {
        $this->api->setAccessToken($accessToken);
        $artists = $this->getMyTopArtists($accessToken);
        $artistsIds = [];
        foreach ($artists->items as $artist) {
            $artistsIds[] = $artist->id;
        }
        if (empty($artistsIds)){
            return [];
        }
        $options = [
            'seed_artists' => array_slice($artistsIds, 0, 5)
        ];
        return $this->api->getRecommendations($options);
    }
}