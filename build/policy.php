<?php

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use RomaricDrigon\MetaYaml\MetaYaml;
use RomaricDrigon\MetaYaml\Exception\NodeValidatorException;

const WEBSITE_DIRECTORY = 'vendor/drutiny/website';
const API_DIRECTORY = WEBSITE_DIRECTORY . '/api/v2';
const POLICY_SCHEMA = 'https://raw.githubusercontent.com/drutiny/drutiny/2.3.x/src/policy.schema.yml';

if (!is_dir(WEBSITE_DIRECTORY)) {
  echo "ERROR: Website directory has not installed correctly. Please run composer install.\n";
  exit(1);
}

require 'vendor/autoload.php';

$finder = new Finder();
$finder->in('Policy')
  ->files()
  ->name('*.policy.yml');

$list = [];
$failed = [];

$schema = file_get_contents(POLICY_SCHEMA);
$schema = new MetaYaml(Yaml::parse($schema));

foreach ($finder as $file) {
  try {
    $payload = Yaml::parseFile($file->getRealpath());
    $schema->validate($payload);
  }
  catch (NodeValidatorException $e) {
    echo $file->getRealpath() . ': ' . $e->getMessage() . PHP_EOL;
    $failed[] = $file->getRealpath();
    continue;
  }
  catch (ParseException $e) {
    echo $file->getRealpath() . ': ' . $e->getMessage() . PHP_EOL;
    $failed[] = $file->getRealpath();
    continue;
  }

  if (!isset($payload['language'])) {
    $payload['language'] = 'en';
  }
  if (!isset($payload['type'])) {
    $payload['type'] = 'audit';
  }

  $payload['signature'] = hash('sha1', Yaml::dump($payload));

  $api_policy_directory = implode('/', [API_DIRECTORY, $payload['language'], 'policy']);
  if (!is_dir($api_policy_directory) && !mkdir($api_policy_directory, 0744, TRUE)) {
    echo "ERROR: Cannot create API directory: $api_policy_directory\n";
    exit(2);
  }

  $filename = $api_policy_directory . '/' . $payload['name'] . '.json';
  file_put_contents($filename, json_encode($payload));
  echo "Written $filename\n";

  $list[$payload['language']][] = [
    'title' => $payload['title'],
    'name' => $payload['name'],
    'type' => $payload['type'],
    'description' => $payload['description'],
    'signature' => $payload['signature'],
    'class' => $payload['class'],
    '_links' => [
      'self' => [
        'href' => str_replace(WEBSITE_DIRECTORY, '', $filename),
      ]
    ]
  ];
}

foreach ($list as $lang => $policy_list) {
  $api_policy_directory = implode('/', [API_DIRECTORY, $lang, 'policy']);
  file_put_contents($api_policy_directory . '/index.json', json_encode($policy_list));
  echo "Written $api_policy_directory/index.json\n";
}

if (count($failed)) {
  echo "\n\n" . count($failed) . " policy files failed to process:\n";
  echo "- " . implode("\n- ", $failed) . "\n";
  exit(1);
}
