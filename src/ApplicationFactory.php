<?php
use Gitlab\Client;
use Gitlab\Model\Project;

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 06.01.17
 * Time: 12:29
 */
class ApplicationFactory {
    public function createClient ($url) {
        return new Client($url);
    }

    public function loadProject ($id, Client $client) {
        return new Project($id, $client);
    }

    public function createXmlElement ($file) {
        return new SimpleXMLElement(file_get_contents($file));
    }
}