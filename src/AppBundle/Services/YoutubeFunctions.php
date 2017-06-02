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
     * @param $youtubeClientId
     * @param $youtubeClientSecret
     */
    public function __construct($youtubeClientId, $youtubeClientSecret, $youtubeApiKey, $redirectUrl, $router)
    {
        $this->youtubeClientId = $youtubeClientId;
        $this->youtubeClientSecret = $youtubeClientSecret;
        $this->youtubeApiKey = $youtubeApiKey;
        $this->router = $router;
        $this->redirectUrl = $redirectUrl;

        $this->client = new \Google_Client();
        $this->youtube = new \Google_Service_YouTube($this->client);
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
     * @param $code
     * @return array Token
     */
    public function getToken($code)
    {
        if($this->client->isAccessTokenExpired()){
           // $this->client->refreshToken($code);
           //$token = $this->client->setAccessToken();
        }else{
            $this->client->fetchAccessTokenWithAuthCode($code);

            return $this->client->getAccessToken();
        }
    }

    /**
     * @param $keyword
     * @return \Google_Service_YouTube_SearchListResponse
     */
    public function search($keyword)
    {

        return $this->youtube->search->listSearch('id,snippet', ['q' => $keyword, 'order' => 'relevance', 'maxResults' => 6, 'type' => 'video']);
    }

    /**
     * @param $videoId
     * @return \Google_Service_YouTube_VideoListResponse
     */
    public function video($videoId)
    {

        return $this->youtube->videos->listVideos('id,snippet,statistics', ['id' => $videoId]);

    }


}
