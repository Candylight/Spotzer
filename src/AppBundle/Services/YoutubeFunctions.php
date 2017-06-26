<?php
/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:11
 */

namespace AppBundle\Services;


use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class YoutubeFunctions
{
    private $youtubeClientSecret;
    private $youtubeClientId;
    private $youtubeApiKey;
    private $client;
    private $youtube;
    private $code;
    private $redirectUrl;
    private $router;


    /**
     * YoutubeFunctions constructor.
     * @param string $youtubeClientId
     * @param string $youtubeClientSecret
     */
    public function __construct($youtubeClientId, $youtubeClientSecret, $youtubeApiKey, $redirectUrl, $router)
    {
        $this->youtubeClientId = $youtubeClientId;
        $this->youtubeClientSecret = $youtubeClientSecret;
        $this->youtubeApiKey = $youtubeApiKey;
        $this->router = $router;
        $this->redirectUrl = $redirectUrl;

        $this->client = new \Google_Client();
        $this->client->setDeveloperKey($this->youtubeApiKey);
        $this->client->setApplicationName('Spotzer');
        $this->client->setClientId($this->youtubeClientId);
        $this->client->setClientSecret($this->youtubeClientSecret);
        $this->client->setAccessType("offline");    //offline access
        $this->client->setIncludeGrantedScopes(true);  //incremental auth
        $this->client->addScope(\Google_Service_YouTube::YOUTUBE_FORCE_SSL);
        $this->client->setRedirectUri($this->router->generate($this->redirectUrl, array(), UrlGeneratorInterface::ABSOLUTE_URL));
    }

    /**
     *
     * @return string  HttpResponse
     */
    public function getAuthorizationUrl()
    {
        return $this->client->createAuthUrl();
    }


    /**
     * @param string $code
     * @return array Token
     */
    public function getToken($code)
    {
        $this->client->fetchAccessTokenWithAuthCode($code);

        return $this->client->getAccessToken();
    }

    /**
     * @param string $token
     * @param string $refreshToken
     * @return array RefreshToken
     */
    public function getRefreshToken($token, $refreshToken)
    {
        $this->client->setAccessToken($token);

        if($this->client->isAccessTokenExpired()){
            $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

            return  $this->client->refreshToken($refreshToken);
        }
    }

    /**
     * @param string $keyword
     * @return \Google_Service_YouTube_SearchListResponse
     */
    public function search($keyword)
    {
        $this->youtube = new \Google_Service_YouTube($this->client);

        return $this->youtube->search->listSearch('id,snippet', ['q' => $keyword, 'order' => 'relevance', 'maxResults' => 6, 'type' => 'video']);
    }

    /**
     * @param $videoId
     * @return \Google_Service_YouTube_VideoListResponse
     */
    public function video($videoId)
    {
        $this->youtube = new \Google_Service_YouTube($this->client);

        return $this->youtube->videos->listVideos('id,snippet,statistics', ['id' => $videoId]);

    }

    /**
     * @param $token
     * @param $title
     * @return \Google_Service_YouTube_Playlist
     */
    public function createPlaylist($token, $title){

        $this->client->setAccessToken($token);
        $youtube = new \Google_Service_YouTube($this->client);

        // 1. Create the snippet for the playlist. Set its title and description.
        $playlistSnippet = new \Google_Service_YouTube_PlaylistSnippet();
        $playlistSnippet->setTitle($title .' '. date("Y-m-d H:i:s"));
        $playlistSnippet->setDescription('A private playlist created with the YouTube API v3');


        // 2. Define the playlist's status.
        $playlistStatus = new \Google_Service_YouTube_PlaylistStatus();
        $playlistStatus->setPrivacyStatus('private');

        // 3. Define a playlist resource and associate the snippet and status
        // defined above with that resource.
        $youTubePlaylist = new \Google_Service_YouTube_Playlist();
        $youTubePlaylist->setSnippet($playlistSnippet);
        $youTubePlaylist->setStatus($playlistStatus);


        return $youtube->playlists->insert('snippet,status', $youTubePlaylist, array());

    }
  
    /**
     * @return \Google_Service_YouTube_PlaylistListResponse
     */
   public function getPlaylist($token)
    {
        $this->client->setAccessToken($token);
        $youtube = new \Google_Service_YouTube($this->client);

        return $youtube->playlists->listPlaylists('snippet,contentDetails', ['mine' => true, 'maxResults' => 25] );
    }
}



