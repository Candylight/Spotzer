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

        $options = ['scope' => ['user-read-email'],];

        return $this->session->getAuthorizeUrl($options);
    }

    public function getToken($code)
    {
        $this->session->requestAccessToken($code);

        return $this->session->getAccessToken();

    }


}