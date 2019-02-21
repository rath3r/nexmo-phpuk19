<?php

require 'vendor/autoload.php';

$config = parse_ini_file('config.ini', true);

$basic  = new \Nexmo\Client\Credentials\Basic($config['nexmo']['key'], $config['nexmo']['secret']);
$client = new \Nexmo\Client($basic);

$html = 'API-v2.html';
$doc = new DOMDocument();
$doc->loadHTMLFile($html);

$xpath = new DOMXPath($doc);
$startDates = $xpath->query('//ul/li[1]/ul/li[*]/ul/li[5]/text()');
$now = new DateTime();

foreach ($startDates as $index => $startDate) {
    $start = strtotime(trim($startDate->nodeValue));

    $durationNode = $xpath->query(sprintf('//ul/li[1]/ul/li[%s]/ul/li[6]/text()', $index + 1));
    $duration = trim($durationNode->item(0)->nodeValue);
    $end = strtotime(sprintf('+%s minutes', $duration), $start);

    $title = '';
    if($start >= time() && $end <= time()){
      $titleNode = $xpath->query(sprintf('//ul/li[1]/ul/li[%s]/ul/li[1]/text()', $index + 1));
      $title = trim($titleNode->item(0)->nodeValue);
    }
}

$phoneNumber = $config['phone']['number'];
$from = 'Nexmo Hackathon';
if($title) {
    $text = sprintf('The current talk is: %s', $title);
} else {
    $text = 'There is nothing on at the moment';
}

$message = $client->message()->send([
    'to' => $phoneNumber,
    'from' => $from,
    'text' => $text
]);
