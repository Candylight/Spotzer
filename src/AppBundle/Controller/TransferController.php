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
        // tableau de result
        // tableau["success"] = []
        // tableau["erreur"] = []
        if ($request->isXmlHttpRequest()) {
            // retrieve playlist parameter from ajax request
            $plateform_start = $request->query->get('plateform_start');
            $playlist = $request->query->get('playlist');
            $plateform_end = $request->query->get('plateform_end');

            switch ($plateform_start) {
                case 'youtube':
                    $playlistYoutubeName = $this->get('youtube_functions')->getPlaylistById($this->getUser()->getCredentials()->getYoutubeToken(), $playlist)->getItems()[0]['modelData']['snippet']['title'];
                    $tracks = $this->get('youtube_functions')->getPlaylistItems($this->getUser()->getCredentials()->getYoutubeToken(), $playlist);
                    if ($plateform_end == 'spotify') {
                        $spotifyPlaylist = $this->get('spotify_functions')->createPlaylist($this->getUser()->getCredentials()->getSpotifyToken(),$playlistYoutubeName );
                        foreach ($tracks as $track) {
                            $sanitizeTitle = preg_replace('/\([^)]+\)/','',$track->getSnippet()->title);
                            $sanitizeTitle = preg_replace('/\[[^)]+\]/', '', $sanitizeTitle);
                            $sanitizeTitle = preg_replace('/[^A-Za-z0-9\s]/', '', $sanitizeTitle);
                            if ($sanitizeTitle !== ''){
                                $spotifyTracks = $this->get('spotify_functions')->searchBestResult($this->getUser()->getCredentials()->getSpotifyToken(), $sanitizeTitle);
                                foreach ($spotifyTracks as $item){
                                    if (!empty($item->items)){
                                        $trackId = $item->items[0]->id;
                                        $this->get('spotify_functions')->addItemToPlaylist($this->getUser()->getCredentials()->getSpotifyToken(), $spotifyPlaylist->id, $trackId);
                                    }
                                }
                            }
                        }
                    }
                break;
                case 'spotify':
                    $playlistSpotifyName = $this->get('spotify_functions')->getPlaylistByID($this->getUser()->getCredentials()->getSpotifyToken(), $playlist)->name;
                    $tracks = $this->get('spotify_functions')->getPlaylistItem($this->getUser()->getCredentials()->getSpotifyToken(), $playlist)->items;
                    if ($plateform_end == 'youtube') {
                        $youtubePlaylist = $this->get('youtube_functions')->createPlaylist($this->getUser()->getCredentials()->getYoutubeToken(), $playlistSpotifyName);
                        foreach ($tracks as $track) {
                            $youtubeTracks = $this->get('youtube_functions')->searchBestResult($track->track->name . ' ' . $track->track->artists[0]->name);
                            foreach ($youtubeTracks as $item) {
                                $trackId = $item->getId()->videoId;
                            }
                            $this->get('youtube_functions')->addItemToPlaylist($this->getUser()->getCredentials()->getYoutubeToken(), $youtubePlaylist->id, $trackId);
                        }
                    } elseif ($plateform_end == 'deezer'){
                        $deezerPlaylist = $this->get('deezer_functions')->createPlaylist($this->getUser()->getCredentials()->getDeezerToken(), $playlistSpotifyName);
                        foreach ($tracks as $track) {
                            $deezerTracks = $this->get('deezer_functions')->searchBestResult($track->track->name . ' ' . $track->track->artists[0]->name);
                            foreach ($deezerTracks as $item) {
                                if (is_array($item) ){
                                    $trackId = $item[0]->id;
                                }
                            }
                            $this->get('deezer_functions')->addItemToPlaylist($this->getUser()->getCredentials()->getDeezerToken(), preg_replace('/\.[^.]*$/', '', $deezerPlaylist->id), $trackId);
                        }
                    }
                break;
                case 'deezer':
                    // Création de la playlist sur la plateforme de fin
                    // Récupération de toutes les tracks de la playlist
                    // for(tracks)
                        // get nom artiste
                        // get nom album
                        // Recherche de nom+album dans plateforme de fin
                        // SI EXISTE
                            // Ajout de la track dans la playlist venant d'être créée
                            // Ajout valeur de succès dans un tableau
                        // SI EXISTE PAS
                            // Ajout d'une valeur d'erreur dans un tableau
            }

            return $this->render('transfer/ajax/result.html.twig', [
            ]);
        }
    }
}