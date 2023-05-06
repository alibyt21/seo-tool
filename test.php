<?php

include_once 'vendor/autoload.php';
use Goutte\Client;
$client = new Client();
$currentQuery = 'ساخت حساب کاربری اینستاگرام';

$crawler = $client->request('GET', "https://www.google.com/search?q={$currentQuery}");
$crawler->filter('.egMi0 > a')->each(function ($node){
    $next = $node->attr('href');
    echo "<pre>";
    var_dump($next);

    echo "</pre>";

});

var_dump($crawler->html());



