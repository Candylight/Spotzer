<?php
/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:11
 */

namespace AppBundle\Services;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class YoutubeFunctions
{
    private $youtubeClientSecret;
    private $youtubeClientId;
    private $client;
    private $code;

    /**
     * YoutubeFunctions constructor.
     */
    public function __construct($youtubeClientId, $youtubeClientSecret)
    {
        $this->youtubeClientId = $youtubeClientId;
        $this->youtubeClientSecret = $youtubeClientSecret;

        $this->client = new \Google_Client();

    }

    public function getAuthorizationUrl($redirectUrl){


        $this->client->setApplicationName('Spotzer');
        $this->client->setClientId($this->youtubeClientId);
        $this->client->setClientSecret($this->youtubeClientSecret);
        $this->client->setAccessType("offline");    //offline access
        $this->client->setIncludeGrantedScopes(true);  //incremental auth
        $this->client->addScope(\Google_Service_YouTube::YOUTUBE_FORCE_SSL);
        $this->client->setRedirectUri($redirectUrl);

        return  $this->client->createAuthUrl();

    }
    
    public function getToken($code){

        $this->client->authenticate($code);

        return $this->client->getAccessToken();

    }



}