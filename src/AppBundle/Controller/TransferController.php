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
                    $youtubePLaylists = $this->get('youtube_functions')->getPlaylist($this->getUser()->getCredentials());
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
                case 'deezer':
                    $deezerPlaylists = $this->get('deezer_functions')->getPlaylist($this->getUser()->getCredentials()->getDeezerToken())->data;
                    return $this->render('transfer/ajax/playlist/deezerPlaylists.html.twig', [
                        'deezerPlaylists' => $deezerPlaylists,
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
        $errorLogs = [];
        $successLogs = [];
        if ($request->isXmlHttpRequest()) {
            // retrieve playlist parameter from ajax request
            $plateform_start = $request->query->get('plateform_start');
            $playlist = $request->query->get('playlist');
            $plateform_end = $request->query->get('plateform_end');

            switch ($plateform_start) {
                case 'youtube':
                    $playlistYoutubeName = $this->get('youtube_functions')->getPlaylistById($this->getUser()->getCredentials(), $playlist)->getItems()[0]['modelData']['snippet']['title'];
                    $tracks = $this->get('youtube_functions')->getPlaylistItems($this->getUser()->getCredentials(), $playlist);
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
                                        $successLogs[] = $track->getSnippet()->title;
                                    } else {
                                        $errorLogs[] = $track->getSnippet()->title;
                                    }
                                }
                            }
                        }
                    } elseif ($plateform_end == 'deezer') {
                        $deezerPlaylist = $this->get('deezer_functions')->createPlaylist($this->getUser()->getCredentials()->getDeezerToken(), $playlistYoutubeName);
                        foreach ($tracks as $track) {
                            $sanitizeTitle = preg_replace('/\([^)]+\)/','',$track->getSnippet()->title);
                            $sanitizeTitle = preg_replace('/\[[^)]+\]/', '', $sanitizeTitle);
                            $sanitizeTitle = preg_replace('/[^A-Za-z0-9\s]/', '', $sanitizeTitle);
                            sleep(1);
                            if ($sanitizeTitle !== ''){
                                $deezerTracks = $this->get('deezer_functions')->searchBestResult($sanitizeTitle)->data;
                                if (!empty($deezerTracks)){
                                    foreach ($deezerTracks as $item){
                                        $trackId = $item->id;
                                        $this->get('deezer_functions')->addItemToPlaylist($this->getUser()->getCredentials()->getDeezerToken(), preg_replace('/\.[^.]*$/', '', $deezerPlaylist->id), $trackId);
                                        $successLogs[] = $track->getSnippet()->title;
                                    }
                                } else {
                                    $errorLogs[] = $track->getSnippet()->title;
                                }
                            } else {
                                $errorLogs[] = $track->getSnippet()->title;
                            }
                        }
                    }
                break;
                case 'spotify':
                    $playlistSpotifyName = $this->get('spotify_functions')->getPlaylistByID($this->getUser()->getCredentials()->getSpotifyToken(), $playlist)->name;
                    $tracks = $this->get('spotify_functions')->getPlaylistItem($this->getUser()->getCredentials()->getSpotifyToken(), $playlist)->items;
                    if ($plateform_end == 'youtube') {
                        $youtubePlaylist = $this->get('youtube_functions')->createPlaylist($this->getUser()->getCredentials(), $playlistSpotifyName, '', 'private');
                        foreach ($tracks as $track) {
                            $youtubeTracks = $this->get('youtube_functions')->searchBestResult($track->track->name . ' ' . $track->track->artists[0]->name);
                            foreach ($youtubeTracks as $item) {
                                $trackId = $item->getId()->videoId;
                            }
                            $this->get('youtube_functions')->addItemToPlaylist($this->getUser()->getCredentials(), $youtubePlaylist->id, $trackId);
                            $successLogs[] = $track->track->name . ' ' . $track->track->artists[0]->name;
                        }
                    } elseif ($plateform_end == 'deezer'){
                        $deezerPlaylist = $this->get('deezer_functions')->createPlaylist($this->getUser()->getCredentials()->getDeezerToken(), $playlistSpotifyName);
                        foreach ($tracks as $track) {
                            sleep(1);
                            $deezerTracks = $this->get('deezer_functions')->searchBestResult($track->track->name . ' ' . $track->track->artists[0]->name)->data;
                            if (!empty($deezerTracks)) {
                                foreach ($deezerTracks as $item) {
                                    $trackId = $item->id;
                                    $this->get('deezer_functions')->addItemToPlaylist($this->getUser()->getCredentials()->getDeezerToken(), preg_replace('/\.[^.]*$/', '', $deezerPlaylist->id), $trackId);
                                    $successLogs[] = $track->track->name . ' ' . $track->track->artists[0]->name;
                                }
                            } else {
                                $errorLogs[] = $track->track->name . ' ' . $track->track->artists[0]->name;
                            }
                        }
                    }
                break;
                case 'deezer':
                    $playlistDeezer = $this->get('deezer_functions')->getPlaylistById($this->getUser()->getCredentials()->getDeezerToken(), $playlist);
                    $playlistDeezerName = $playlistDeezer->title;
                    $tracks = $playlistDeezer->tracks->data;
                    if ($plateform_end == 'spotify'){
                        $spotifyPlaylist = $this->get('spotify_functions')->createPlaylist($this->getUser()->getCredentials()->getSpotifyToken(),$playlistDeezerName );
                        foreach ($tracks as $track){
                            $spotifyTracks = $this->get('spotify_functions')->searchBestResult($this->getUser()->getCredentials()->getSpotifyToken(), $track->title.' '.$track->artist->name);
                            foreach ($spotifyTracks as $item){
                                if (!empty($item->items)){
                                    $trackId = $item->items[0]->id;
                                    $this->get('spotify_functions')->addItemToPlaylist($this->getUser()->getCredentials()->getSpotifyToken(), $spotifyPlaylist->id, $trackId);
                                    $successLogs[] = $track->title.' '.$track->artist->name;
                                } else {
                                    $errorLogs[] = $track->title.' '.$track->artist->name;
                                }
                            }
                        }
                    } elseif ($plateform_end == 'youtube'){
                        $youtubePlaylist = $this->get('youtube_functions')->createPlaylist($this->getUser()->getCredentials(), $playlistDeezerName, '', 'private');
                        foreach ($tracks as $track) {
                            $youtubeTracks = $this->get('youtube_functions')->searchBestResult($track->title.' '.$track->artist->name);
                            foreach ($youtubeTracks as $item) {
                                $trackId = $item->getId()->videoId;
                            }
                            $this->get('youtube_functions')->addItemToPlaylist($this->getUser()->getCredentials(), $youtubePlaylist->id, $trackId);
                            $successLogs[] = $track->title.' '.$track->artist->name;
                        }
                    }
            }

            return $this->render('transfer/ajax/result.html.twig', [
                'successLogs' => $successLogs,
                'errorLogs' => $errorLogs
            ]);
        }
    }
}