<?php

/**
 * Symfony service to wrap curl calls
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

class CurlProxy
{
    /**
     * retrieve url content
     * @params $url string
     */
    public function get($url)
    {
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // grab URL and pass it to the browser
        $content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // close cURL resource, and free up system resources
        if ($content === false) {
            throw new \Exception("CurlProxy error: " . curl_error($ch) . " when calling $url");
        }
        curl_close($ch);

        return $http_code == 200 && !empty($content) ? $content : false;
    }
}
