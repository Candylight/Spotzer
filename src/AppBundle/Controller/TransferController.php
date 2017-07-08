<?php
/**
 * Created by PhpStorm.
 * User: Thibault
 * Date: 10/02/2017
 * Time: 12:46
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransferController extends Controller
{

    /**
     * @Route("/transfer", name="transfer")
     */
    public function indexAction(Request $request)
    {

        // replace this example code with whatever you need
        return $this->render('transfer/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/transfer/getplaylists", name="transfer_getplaylists")
     */
    public function getPlateformePlayListAction(Request $request)
    {
        //ajax ?
        if ($request->isXmlHttpRequest()) {
            // retrieve playlist parameter from ajax request
            $playlists = $request->query->get('playlist');
            switch ($playlists) {
                case 'youtube':
                    $youtubePLaylists = $this->get('youtube_functions')->getPlaylist($this->getUser()->getCredentials()->getYoutubeToken());
                    return $this->render('transfer/ajax/playlist/youtubePlaylists.html.twig', [
                        'youtubePlaylists' => $youtubePLaylists,
                    ]);
                    break;
                case 'spotify':
                    $spotifyPlaylists = $this->get('spotify_functions')->getUserPlaylist($this->getUser()->getCredentials()->getSpotifyToken())->items;
                    return $this->render('transfer/ajax/playlist/spotifyPlaylists.html.twig', [
                        'spotifyPlaylists' => $spotifyPlaylists,
                    ]);
                    break;
            }
        }
    }

    /**
     * @Route("/transfer/launch", name="transfer_launch")
     */
    public function launchAction(Request $request)
    {
        return $this->render('transfer/ajax/result.html.twig', [
            'result' => "",
        ]);
    }
}