<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
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