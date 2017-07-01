<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
     * @return httpResponse
     */
    public function indexAction()
    {

        return $this->redirect($this->get('deezer_functions')->getAuthorizationUrl());
    }

    /**
     * @Route("/deezer/callback", name="deezer_callback")
     *
     *
     * @return httpResponse
     */
    public function callbackAction()
    {
        $token = $this->get('deezer_functions')->getToken($_GET['code']);

        $this->getUser()->getCredentials()->setDeezerToken($token);
        $this->getDoctrine()->getManager()->persist($this->getUser());
        $this->getDoctrine()->getManager()->flush();


        return $this->redirectToRoute('homepage');
    }
}