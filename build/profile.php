<?php

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

const WEBSITE_DIRECTORY = 'vendor/drutiny/website';
const API_DIRECTORY = WEBSITE_DIRECTORY . '/api/v2';

if (!is_dir(WEBSITE_DIRECTORY)) {
  echo "ERROR: Website directory has not installed correctly. Please run composer install.\n";
  exit(1);
}

require 'vendor/autoload.php';

$finder = new Finder();
$finder->in('Profile')
  ->files()
  ->name('*.profile.yml');

$list = [];
$failed = [];

foreach ($finder as $file) {
  try {
    $payload = Yaml::parseFile($file->getRealpath());
  }
  catch (ParseException $e) {
    echo $file->getRealpath() . ': ' . $e->getMessage() . PHP_EOL;
    $failed[] = $file->getRealpath();
    continue;
  }
  $payload['name'] = pathinfo($file->getRealpath(), PATHINFO_FILENAME);

  if (!isset($payload['language'])) {
    $payload['language'] = 'en';
  }
  if (!isset($payload['policies'])) {
    $payload['policies'] = [];
  }
  if (!isset($payload['description'])) {
    $payload['description'] = '';
  }
  $payload['signature'] = hash('sha1', Yaml::dump($payload));

  $api_profile_directory = implode('/', [API_DIRECTORY, $payload['language'], 'profile']);
  if (!is_dir($api_profile_directory) && !mkdir($api_profile_directory, 0744, TRUE)) {
    echo "ERROR: Cannot create API directory: $api_profile_directory\n";
    exit(2);
  }

  $filename = $api_profile_directory . '/' . $payload['name'] . '.json';
  file_put_contents($filename, json_encode($payload));
  echo "Written $filename\n";

  $list[$payload['language']][] = [
    'title' => $payload['title'],
    'name' => $payload['name'],
    'description' => $payload['description'],
    'signature' => $payload['signature'],
    'policies' => array_keys($payload['policies']),
    '_links' => [
      'self' => [
        'href' => str_replace(WEBSITE_DIRECTORY, '/', $filename),
      ]
    ]
  ];
}

foreach ($list as $lang => $profile_list) {
  $api_profile_directory = implode('/', [API_DIRECTORY, $lang, 'profile']);
  file_put_contents($api_profile_directory . '/index.json', json_encode($profile_list));
  echo "Written $api_profile_directory/index.json\n";
}

if (count($failed)) {
  echo "\n\n" . count($failed) . " profile files failed to process:\n";
  echo "-" . implode("\n-", $failed) . "\n";
  exit(1);
}
