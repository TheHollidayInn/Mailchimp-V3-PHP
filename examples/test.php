<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

define("API_KEY", getenv('API_KEY'));
define("DC", getenv('DC'));

$MC = new Mailchimp(API_KEY, DC);
$lists = $MC->getLists();

var_dump($lists);

 ?>
