<?php
/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:11
 */

namespace AppBundle\Services;


use AppBundle\Entity\Credentials;
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

        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

            return $this->client->refreshToken($refreshToken);
        }
    }

    /**
     * @param Credentials $credentials
     * @return bool
     */
    public function checkTokenValidity($credentials)
    {

        if ($credentials->getYoutubeToken() != null) {
            ;
            if ($credentials->getYoutubeExpireAt() > new \DateTime()) {
                return true;
            }
        }
        return false;

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

    public function searchBestResult($keyword)
    {
        $this->youtube = new \Google_Service_YouTube($this->client);

        return $this->youtube->search->listSearch('id,snippet', ['q' => $keyword, 'maxResults' => 1, 'type' => 'video']);
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
     * @return \Google_Service_YouTube_VideoCategoryListResponse
     */
    public function getVideoCategory()
    {
        $this->youtube = new \Google_Service_YouTube($this->client);

        return $this->youtube->videoCategories->listVideoCategories('snippet', ['regionCode' => 'FR']);
    }

    /**
     * @return \Google_Service_YouTube_VideoListResponse
     */
    public function getVideoByLike($credentials)
    {
        $this->youtube = $this->createServiceYoutubeObject($credentials);

        return $this->youtube->videos->listVideos('id,snippet,statistics', ['myRating' => 'like', 'maxResults' => 7]);

    }

    /**
     * @return \Google_Service_YouTube_VideoListResponse
     */
    public function getMostPopularVideo()
    {
        $this->youtube = new \Google_Service_YouTube($this->client);

        return $this->youtube->videos->listVideos('id, snippet', ['chart' => 'mostPopular', 'maxResults' => 6]);

    }


    /**
     * @param $token
     * @param $title
     * @return \Google_Service_YouTube_Playlist
     */
    public function createPlaylist($credentials, $title, $desc, $status)
    {

        $this->youtube = $this->createServiceYoutubeObject($credentials);

        // 1. Create the snippet for the playlist. Set its title and description.
        $playlistSnippet = new \Google_Service_YouTube_PlaylistSnippet();
        $playlistSnippet->setTitle($title);
        $playlistSnippet->setDescription($desc);


        // 2. Define the playlist's status.
        $playlistStatus = new \Google_Service_YouTube_PlaylistStatus();
        $playlistStatus->setPrivacyStatus($status);

        // 3. Define a playlist resource and associate the snippet and status
        // defined above with that resource.
        $youTubePlaylist = new \Google_Service_YouTube_Playlist();
        $youTubePlaylist->setSnippet($playlistSnippet);
        $youTubePlaylist->setStatus($playlistStatus);


        return $this->youtube->playlists->insert('snippet,status', $youTubePlaylist, array());

    }

    public function addItemToPlaylist($credentials, $playlistID, $videoID)
    {
        $this->youtube = $this->createServiceYoutubeObject($credentials);
        $youtube = new \Google_Service_YouTube($this->client);
        $playlistItemSnippet = new \Google_Service_YouTube_PlaylistItemSnippet();
        $playlistItemSnippet->setPlaylistId($playlistID);
        $resourceID = new \Google_Service_YouTube_ResourceId();
        $resourceID->setKind('youtube#video');
        $resourceID->setVideoId($videoID);
        $playlistItemSnippet->setResourceId($resourceID);
        $youtubePlaylistItem = new \Google_Service_YouTube_PlaylistItem();
        $youtubePlaylistItem->setSnippet($playlistItemSnippet);

        return $youtube->playlistItems->insert('snippet', $youtubePlaylistItem);
    }

    /**
     * @return \Google_Service_YouTube_PlaylistListResponse
     */
    public function getPlaylist($credentials)
    {
        $this->youtube = $this->createServiceYoutubeObject($credentials);

        return $this->youtube->playlists->listPlaylists('snippet,contentDetails', ['mine' => true, 'maxResults' => 25]);
    }

    /**
     * @param $token
     * @param $playlistId
     * @return \Google_Service_YouTube_PlaylistItemListResponse
     */
    public function getPlaylistItems($credentials, $playlistId)
    {

        $this->youtube = $this->createServiceYoutubeObject($credentials);

        return $this->youtube->playlistItems->listPlaylistItems('snippet,contentDetails', ['playlistId' => $playlistId, 'maxResults' => 50]);
    }

    /**
     * @param $token
     * @return \Google_Service_YouTube_SubscriptionListResponse
     */
    public function getSubscription($credentials)
    {
        $this->youtube = $this->createServiceYoutubeObject($credentials);

        return $this->youtube->subscriptions->listSubscriptions('snippet, contentDetails,subscriberSnippet', ['mine' => true, 'maxResults' => 50]);
    }


    /**
     * @param $token
     * @return \Google_Service_YouTube_ChannelListResponse
     */
    public function getMyChannels($credentials)
    {
        $this->youtube = $this->createServiceYoutubeObject($credentials);


        return $this->youtube->channels->listChannels('snippet, contentDetails, statistics, status, topicDetails', ['mine' => true, 'maxResults' => 50]);
    }

    public function getPlaylistById($credentials, $playlistId)
    {
        $this->youtube = $this->createServiceYoutubeObject($credentials);

        $youtube = new \Google_Service_YouTube($this->client);
        return $youtube->playlists->listPlaylists('snippet,contentDetails', ['id' => $playlistId, 'maxResults' => 25] );
    }


    /**
     * @param Credentials $credentials
     * @return \Google_Service_YouTube
     */
    public function createServiceYoutubeObject($credentials)
    {
        if ($credentials->getYoutubeToken() != null) {
            if ($credentials->getYoutubeExpireAt() > new \DateTime()) {
                $this->client->setAccessToken($credentials->getYoutubeToken());
                $this->youtube = new \Google_Service_YouTube($this->client);
            } else {
                $this->client->fetchAccessTokenWithRefreshToken($credentials->getYoutubeRefreshToken());
                $this->client->refreshToken($credentials->getYoutubeRefreshToken());
                $this->youtube = new \Google_Service_YouTube($this->client);
            }
        }
        return $this->youtube;
    }
}



