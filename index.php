<?php
require_once("config.php");

require(__DIR__ . "/vendor/autoload.php");

use Firebase\FirebaseLib;
use GitHubWebhook\Handler;


$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);

$handler = new Handler(SECRET, __DIR__);
if($handler->handle()) {
        $data = $handler->getData();
        var_dump($data["repository"]);


        $projectName = $data["repository"]["name"];
        $dayId           = date("dmY");
        $commitsPerPush = sizeof($data["commits"]);



        $project = $firebase->get(DEFAULT_PATH . '/repo/' . $projectName);
        $day     = $firebase->get(DEFAULT_PATH . '/daily/' . $dayId);

        if($project == "null") {
                $project = $firebase->set(DEFAULT_PATH . '/repo/' . $projectName, 'test');
        }

        if($day == "null") {
                $firebase->set(DEFAULT_PATH . '/daily/' . $dayId , $commitsPerPush);
        } else {
                $firebase->set(DEFAULT_PATH . '/daily/' . $dayId , $day + $commitsPerPush);
        }
}
