<?php
/**
 * Created by PhpStorm.
 * User: konra
 * Date: 06.02.2018
 * Time: 16:34
 */

namespace AppBundle\Controller;

const API_KEY = '***';

use Google_Client;


class YoutubeController
{
    /**
     * @param $url
     * @return mixed
     */
    public function dataFromApiAction($url){
        $client = new Google_Client();

        $client->setApplicationName("YTDummy");
        $client->setDeveloperKey(API_KEY);

        $youtube = new \Google_Service_YouTube($client);

       $snippet = $youtube->videos->listVideos('snippet, recordingDetails',
                array(
                    'id' => $url
                ))->getItems()[0]->getSnippet();




        $videoResponse = $snippet->getTitle();

        if($snippet->getCategoryId() != 10){
            return false;
        } else{
            return $videoResponse;
        }
    }

    /**
     * @param $url
     * @return mixed
     */
    public function checkYoutube($url){
        $re = '/http(?:s?):\/\/(?:www\.)?youtu(?:be\.com\/watch\?v=|\.be\/)([\w\-\_]*)(&(amp;)?‌​[\w\?‌​=]*)?/';
        $str = $url;
        $subst = '$1';

        $result = preg_replace($re, $subst, $str);

        if(strpos($result, 'http') !== false){
            return false;
        } else{
            return $result;
        }
    }

}