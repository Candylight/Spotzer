<?php
/**
 * Created by PhpStorm.
 * User: Thibault
 * Date: 10/02/2017
 * Time: 12:46
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Search;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends Controller
{

    /**
     * @Route("/search", name="search")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return $this->render('search/index.html.twig', [
            'keyword' => $request->get('keyword',"")
        ]);
    }


    /**
     * @Route("/search/songofalbum", name="search_song_album")
     */
    public function songOfAlbumAction(Request $request)
    {
        $songs = array();

        if ($request->isXmlHttpRequest()) {
            // retrieve playlist parameter from ajax request
            $album = $request->query->get('album');

            if($this->getUser()->getSpotifyPrefered())
            {
                if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials()))
                {
                    $songs = $this->get('spotify_functions')->getAlbumSongs($this->getUser()->getCredentials()->getSpotifyToken(),$album);

                    return $this->render('search/ajax/songsalbum/spotify.html.twig', array(
                        'songs' => $songs,
                    ));
                }
            }
            elseif ($this->getUser()->getDeezerPrefered())
            {
                if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials()))
                {
                    $songs = $this->get('deezer_functions')->getAlbumSongs($album);

                    return $this->render('search/ajax/songsalbum/deezer.html.twig', array(
                        'songs' => $songs,
                    ));
                }
            }
        }

        return $this->render('search/ajax/songsalbum/spotify.html.twig', [
            'songs' => $songs,
        ]);
    }

    /**
     * @Route("/search/track", name="search_track")
     */
    public function getTrackAction(Request $request)
    {
        $response = "";

        if ($request->isXmlHttpRequest()) {
            $trackId = $request->query->get('trackId');
            $platform = $request->query->get('platform');

            if($platform == "spotify")
            {
                $response = "<iframe src=\"https://open.spotify.com/embed?uri=spotify:track:".$trackId."\" frameborder=\"0\" allowtransparency=\"true\"></iframe>";
            }
            else if($platform == "deezer")
            {
                $response = "<iframe scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" src=\"https://www.deezer.com/plugins/player?format=classic&autoplay=false&playlist=false&width=700&height=350&color=007FEB&layout=dark&size=medium&type=tracks&id=".$trackId."\" width=\"700\" height=\"350\"></iframe>";
            }
        }

        return new Response($response);
    }
}