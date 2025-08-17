<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

return [
    'azure_endpoint'     => $_ENV['AZURE_ENDPOINT'],
    'azure_api_key'      => $_ENV['AZURE_API_KEY'],
    'brave_api_key'      => $_ENV['BRAVE_API_KEY'],
    'huggingface_token'  => $_ENV['HUGGINGFACE_TOKEN'],
];
