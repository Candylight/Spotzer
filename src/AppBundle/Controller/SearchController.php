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
            $artist = $request->query->get('artist');
            $album = $request->query->get('album');

            if($this->getUser()->getSpotifyPrefered())
            {
                if($this->get('spotify_functions')->checkTokenValidity($this->getUser()->getCredentials()))
                {

                }
            }
            elseif ($this->getUser()->getDeezerPrefered())
            {
                if($this->get('deezer_functions')->checkTokenValidity($this->getUser()->getCredentials()))
                {
                    $songs = $this->get('deezer_functions')->getAlbumSongs($album);

                    return $this->render('search/ajax/songsalbum/deezer.html.twig', [
                        'songs' => $songs,
                    ]);
                }
            }
        }

        return $this->render('search/ajax/songsalbum/spotify.html.twig', [
            'songs' => $songs,
        ]);
    }



}