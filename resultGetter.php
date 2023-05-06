<?php
$query = 'salam';
$url = "http://localhost/my%20project/goutte/start.php?query=$query";

$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, false);

$data = curl_exec($curl);

curl_close($curl);
$arrayData = explode(',',$data);
var_dump($arrayData);