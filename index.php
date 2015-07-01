<?php
require_once("config.php");

require(__DIR__ . "/vendor/autoload.php");

use Firebase\FirebaseLib;
use GitHubWebhook\Handler;


$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);



$handler = new Handler(SECRET, __DIR__);
if($handler->handle()) {
        $data = $handler->getData();


        $userSender     = $data["sender"];
        $projectName    = $data["repository"]["name"];
        $dayId          = date("dmY");
        $commitsPerPush = sizeof($data["commits"]);



        $project = $firebase->get(DEFAULT_PATH . '/repo/' . $projectName);
        $day     = $firebase->get(DEFAULT_PATH . '/daily/' . $dayId);

        foreach( $data["commits"] as $aCommit ) {
                $aCommit['sender'] = $data["sender"];
                $firebase->push( DEFAULT_PATH . '/history', $aCommit );
        }


        if($project == "null") {
                $dataToSave = array(
                        $dayId => array(
                                'total' => $commitsPerPush,
                                'users' => array(
                                        $userSender["login"] => $commitsPerPush
                                )
                        )
                );

                $firebase->set(DEFAULT_PATH . '/repo/' . $projectName , $dataToSave );

        } else {

                $projectAtDate = $firebase->get(DEFAULT_PATH . '/repo/' . $projectName . '/' . $dayId);

                if($projectAtDate != "null") {

                        $projectAtDateForUser = $firebase->get(DEFAULT_PATH . '/repo/' . $projectName . '/' . $dayId . '/users/' . $userSender["login"] );
                        $projectAtDateTotal = $firebase->get(DEFAULT_PATH . '/repo/' . $projectName . '/' . $dayId . '/total' );
                        $firebase->set(DEFAULT_PATH . '/repo/' . $projectName . '/' . $dayId . '/total' , $projectAtDateTotal + $commitsPerPush );

                        if($projectAtDateForUser == "null") {
                                $firebase->set(DEFAULT_PATH . '/repo/' . $projectName . '/' . $dayId . '/users/' . $userSender["login"] , $commitsPerPush );
                        } else {
                                $firebase->set(DEFAULT_PATH . '/repo/' . $projectName . '/' . $dayId . '/users/' . $userSender["login"] , $projectAtDateForUser + $commitsPerPush );
                        }

                } else {
                        $data = array(
                                'total' => $commitsPerPush,
                                'users' => array(
                                        $userSender["login"] => $commitsPerPush
                                )
                        );

                        $firebase->set(DEFAULT_PATH . '/repo/' . $projectName . '/' . $dayId , $data );
                }
        }


        if($day == "null") {
                $firebase->set(DEFAULT_PATH . '/daily/' . $dayId , $commitsPerPush);
        } else {
                $firebase->set(DEFAULT_PATH . '/daily/' . $dayId , $day + $commitsPerPush);
        }
}
