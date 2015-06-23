<?php
require_once("config.php");

require(__DIR__ . "/vendor/autoload.php");

use Firebase\FirebaseLib;
use GitHubWebhook\Handler;


$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);

// --- storing an array ---
// $test = array(
//     "foo" => "bar",
//     "i_love" => "lamp",
//     "id" => 42
// );
// $dateTime = new DateTime();
// $firebase->set(DEFAULT_PATH . '/' . $dateTime->format('c'), $test);

// // --- storing a string ---
// $firebase->set(DEFAULT_PATH . '/name/contact001', "John Doe");

// --- reading the stored string ---

$projectName = "Documentation";
$dayId 		 = date("dmY");
$commitsPerPush = 2;



$project = $firebase->get(DEFAULT_PATH . '/repo/' . $projectName);
$day 	 = $firebase->get(DEFAULT_PATH . '/daily/' . $dayId);

if($project == "null") {
	$project = $firebase->set(DEFAULT_PATH . '/repo/' . $projectName, 'test');
}

if($day == "null") {
	$firebase->set(DEFAULT_PATH . '/daily/' . $dayId , $commitsPerPush);
} else {
	$firebase->set(DEFAULT_PATH . '/daily/' . $dayId , $day + $commitsPerPush);
}