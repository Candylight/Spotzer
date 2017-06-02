<?php

/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:09
 */

namespace AppBundle\Services;

class LastFMFunctions
{

    private $apiKey;

    /**
     * LastFMFunctions constructor.
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }


    public function search($keyword)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,"http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist=".$keyword."&api_key=".$this->apiKey."&format=json&lang=fr");
        curl_setopt($ch,CURLOPT_HTTPHEADER,array("Accept: application/json","Accept-Language: fr_FR"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }

    public function getContent($keyword)
    {
        $searchResults = json_decode($this->search($keyword),true);

        if(isset($searchResults["error"]))
        {
            return false;
        }

        $searchResults = $searchResults["artist"];
        $content = $searchResults["bio"]["summary"];

        $content = str_replace(substr($content,strpos($content, "<a")),'',$content);

        $link = $searchResults["bio"]["links"]["link"]["href"];
        $link = str_replace(".fm/",".fm/fr/", $link);

        $title = $searchResults["name"];
        $image = $searchResults["image"][2]["#text"];

        return array(
            "title" => $title,
            "description" => $content,
            "image" => $image,
            "link" => $link
        );
    }

    public function searchTopAlbums($keyword)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,"http://ws.audioscrobbler.com/2.0/?method=artist.gettopalbums&artist=".$keyword."&api_key=".$this->apiKey."&format=json&lang=fr&limit=5");
        curl_setopt($ch,CURLOPT_HTTPHEADER,array("Accept: application/json","Accept-Language: fr_FR"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }
}
