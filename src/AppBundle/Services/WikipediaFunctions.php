<?php

/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 15/02/2017
 * Time: 19:09
 */

namespace AppBundle\Services;

class WikipediaFunctions
{
    public function search($keyword)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,"https://fr.wikipedia.org//w/api.php?action=opensearch&format=json&search=".$keyword);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array("Accept: application/json","Accept-Language: fr_FR"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }

    public function getContent($keyword)
    {
        $searchResults = json_decode($this->search($keyword));

        if(isset($searchResults[1][0]) && $searchResults[1][0] != "")
        {
            $title = $searchResults[1][0];
        }
        else
        {
            return false;
        }

        $content = $searchResults[2][0];
        $link = $searchResults[3][0];

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,"https://fr.wikipedia.org/w/api.php?action=query&format=json&prop=pageimages&titles=".str_replace(' ','_',$title."&piprop=original"));
        curl_setopt($ch,CURLOPT_HTTPHEADER,array("Accept: application/json","Accept-Language: fr_FR"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = json_decode(curl_exec($ch),true);

        curl_close($ch);

        $pages = reset($result['query']['pages']);

        $image = "";

        if(isset($pages['original']['source']))
        {
            $image = $pages['original']['source'];
        }

        return array(
            "title" => $title,
            "description" => $content,
            "image" => $image,
            "link" => $link
        );
    }
}
