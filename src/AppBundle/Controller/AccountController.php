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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AccountController extends Controller
{

    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('account/login.html.twig',array(
            "error" => $error,
            "lastUsername" => $lastUsername
        ));
    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction()
    {
        $form = $this->createForm(UserType::class, new User(), array('method' => 'POST', 'action' => $this->generateUrl('saveAccount')));
        return $this->render(':account:registration.html.twig',array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/registration/saveAccount", name="saveAccount")
     * @Method("POST")
     */
    public function saveAccountAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(count($this->getDoctrine()->getRepository('AppBundle:User')->findByUsername($user->getUsername())) > 0)
            {
                /**
                 * TODO: add error message to flashbag
                 */
                return $this->redirectToRoute('login');
            }
            $user->addRole('ROLE_USER');
            $user = $this->encodePassword($user);
            $credentials = new Credentials();
            $credentials->setUser($user);
            if($this->getParameter('mail_activation') === true)
            {
                $this->getDoctrine()->getManager()->persist($user);
                $this->getDoctrine()->getManager()->persist($credentials);
                $this->getDoctrine()->getManager()->flush();
                $this->sendConfirmationMail($user);
                return $this->render('account/confirmMail.html.twig',array(
                    'user' => $user
                ));
            }
            else
            {
                $user->setEnable(1);
                $this->getDoctrine()->getManager()->persist($user);
                $this->getDoctrine()->getManager()->persist($credentials);
                $this->getDoctrine()->getManager()->flush();
                $this->get('session')->getFlashBag()->add('info',$this->get('translator')->trans('user.creation.enabled'));
                $this->authenticateUser($user);

                return $this->redirectToRoute('homepage');
            }
        }
        else
        {
            /**
             * TODO: add error message to flashbag
             */
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/registration/confirmAccount/{code}", name="confirmAccount")
     * @param String $code code de confirmation lié à l'utilisateur
     * @return Response
     */
    public function confirmAccountAction($code)
    {
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('confirmationCode' => $code));
        if(!is_object($user))
        {
            throw new BadRequestHttpException($this->get('translator')->trans('badConfirmationCode',array(),'errors'));
        }

        $now = new \DateTime();
        $interval = $now->diff($user->getDateConfirmationCode());

        if($interval->format('h') >= 24)
        {
            $this->sendConfirmationMail($user);
            return $this->render('account/confirmMail.html.twig',array(
                'error' => true,
                'user' => $user
            ));
        }

        $user->setEnable(1);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add('info',$this->get('translator')->trans('user.creation.enabled'));
        $this->authenticateUser($user);

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/registration/resendMailConfirmation", name="resendMailConfirmation")
     */
    public function resendMailAction(Request $request)
    {
        $user = $this->getDoctrine()->getRepository('User')->find($request->get('userId'));

        if(is_object($user))
        {
            $this->sendConfirmationMail($user);

            return new Response('ok');
        }

        return new Response('nok');
    }

    /**
     * @param User $user
     */
    private function sendConfirmationMail(User $user)
    {
        $user->setConfirmationCode($this->generateConfirmationCode());
        $user->setDateConfirmationCode(new\DateTime());

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('user.creation.mail.title'))
            ->setFrom($this->getParameter('mailer_user'))
            ->setTo($user->getMail())
            ->setBody(
                $this->renderView(
                    'mail/confirmInscription.html.twig',
                    array('user' => $user)
                ),
                'text/html'
            )
        ;
        $this->get('mailer')->send($message);
    }

    private function generateConfirmationCode($length = 25)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function authenticateUser($user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }

    /**
     * @param User $user
     * @return User
     */
    private function encodePassword($user)
    {
        $encoder = $this->container->get('security.password_encoder');
        $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

        return $user;
    }

    /**
     * @Route("/account", name="account")
     */
    public function indexAction()
    {
        return $this->render('account/index.html.twig');
    }
}