<?php

require_once __DIR__ . '/../vendor/autoload.php';

define("API_KEY", "Your api key");
define("DC", "Your dc");

$MC = new Mailchimp(API_KEY, DC);
$lists = $MC->getLists();

var_dump($lists);

 ?>
