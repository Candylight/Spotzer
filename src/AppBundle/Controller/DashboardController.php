<?php
/**
 * Created by PhpStorm.
 * User: Thibault
 * Date: 10/02/2017
 * Time: 12:46
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Credentials;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DashboardController extends Controller
{


    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request)
    {
        return $this->render('dashboard/index.html.twig');
    }


    /**
     * @Route("/dashboard/spotify", name="dashboard_spotify")
     */
    public function spotifyAction(Request $request)
    {
        return $this->render('dashboard/spotify.html.twig',array(
            "connected" => $this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials())
        ));
    }

    /**
     * @Route("/dashboard/deezer", name="dashboard_deezer")
     */
    public function deezerAction(Request $request)
    {
        return $this->render('dashboard/deezer.html.twig', array(
            "connected" => $this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials())
        ));
    }

    /**
     * @Route("/dashboard/youtube", name="dashboard_youtube")
     */
    public function youtubeAction(Request $request)
    {
        return $this->render('dashboard/youtube.html.twig');
    }
}