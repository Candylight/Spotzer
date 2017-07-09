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
        //ajax ?
        if ($request->isXmlHttpRequest()) {
            // retrieve playlist parameter from ajax request
            $artist = $request->query->get('playlist');
            $album = $request->query->get('playlist');
            $plateform = $request->query->get('playlist');
            switch ($plateform) {
                case 'youtube':
                    //$youtubePLaylists = $this->get('youtube_functions')->getPlaylist($this->getUser()->getCredentials()->getYoutubeToken());
                    return $this->render('search/ajax/songsalbum/deezer.html.twig', [
                        'youtubePlaylists' => "",
                    ]);
                    break;
                case 'spotify':
                    //$spotifyPlaylists = $this->get('spotify_functions')->getUserPlaylist($this->getUser()->getCredentials()->getSpotifyToken())->items;
                    return $this->render('search/ajax/songsalbum/spotify.html.twig', [
                        'spotifyPlaylists' => "",
                    ]);
                    break;
            }
        }
    }



}