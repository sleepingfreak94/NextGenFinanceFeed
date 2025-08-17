<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

return [
    'smtp_host' => $_ENV['SMTP_HOST'],
    'smtp_username' => $_ENV['SMTP_USERNAME'],
    'smtp_password' => $_ENV['SMTP_PASSWORD'],
    'smtp_port' => $_ENV['SMTP_PORT'],
    'smtp_from_email' => $_ENV['SMTP_FROM_EMAIL'],
    'smtp_from_name' => $_ENV['SMTP_FROM_NAME'],
    
    'azure_endpoint'     => $_ENV['AZURE_ENDPOINT'],
    'azure_api_key'      => $_ENV['AZURE_API_KEY'],
    'brave_api_key'      => $_ENV['BRAVE_API_KEY'],
    'huggingface_token'  => $_ENV['HUGGINGFACE_TOKEN'],
];
