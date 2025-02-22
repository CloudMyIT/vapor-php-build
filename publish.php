<?php

use Aws\Lambda\LambdaClient;
use Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->safeLoad();

$layers = [
    // Amazon Linux 2:
    'php-81al2' => 'Laravel Vapor PHP 8.1 for Amazon Linux 2',
    'php-82al2' => 'Laravel Vapor PHP 8.2 for Amazon Linux 2',
    'php-82al2-arm' => 'Laravel Vapor PHP 8.2 for Amazon Linux 2 ARM',
    'php-83al2' => 'Laravel Vapor PHP 8.3 for Amazon Linux 2',
    'php-83al2-arm' => 'Laravel Vapor PHP 8.3 for Amazon Linux 2 ARM',
    'php-84al2' => 'Laravel Vapor PHP 8.4 for Amazon Linux 2',
    'php-84al2-arm' => 'Laravel Vapor PHP 8.4 for Amazon Linux 2 ARM',
];

$regions = [
    'us-east-1' => 'US East (N. Virginia) (us-east-1)',
    'us-east-2' => 'US East (Ohio) (us-east-2)',
    'us-west-1' => 'US West (N. California) (us-west-1)',
    'us-west-2' => 'US West (Oregon) (us-west-2)',
    'ap-east-1' => 'Asia Pacific (Hong Kong) (ap-east-1)',
    'ap-south-1' => 'Asia Pacific (Mumbai) (ap-south-1)',
    'ap-northeast-3' => 'Asia Pacific (Osaka) (ap-northeast-3)',
    'ap-northeast-2' => 'Asia Pacific (Seoul) (ap-northeast-2)',
    'ap-southeast-1' => 'Asia Pacific (Singapore) (ap-southeast-1)',
    'ap-southeast-2' => 'Asia Pacific (Sydney) (ap-southeast-2)',
    'ap-southeast-3' => 'Asia Pacific (Jakarta) (ap-southeast-3)',
    'ap-northeast-1' => 'Asia Pacific (Tokyo) (ap-northeast-1)',
    'af-south-1' => 'Africa (Cape Town) (af-south-1)',
    'ca-central-1' => 'Canada (Central) (ca-central-1)',
    // 'cn-north-1' => 'China (Beijing) (cn-north-1)',
    // 'cn-northwest-1' => 'China (Ningxia) (cn-northwest-1)',
    'eu-central-1' => 'EU (Frankfurt) (eu-central-1)',
    'eu-west-1' => 'EU (Ireland) (eu-west-1)',
    'eu-west-2' => 'EU (London) (eu-west-2)',
    'eu-west-3' => 'EU (Paris) (eu-west-3)',
    'eu-north-1' => 'EU (Stockholm) (eu-north-1)',
    'eu-south-1' => 'EU (Milan) (eu-south-1)',
    'me-south-1' => 'Middle East (Bahrain) (me-south-1)',
    'sa-east-1' => 'South America (São Paulo) (sa-east-1)',
];

foreach (array_keys($regions) as $region) {
    $layersToPublish = isset($argv[1]) ? [$argv[1] => $layers[$argv[1]]] : $layers;

    foreach ($layersToPublish as $layer => $description) {
        $lambda = new LambdaClient([
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key' => $_SERVER['AWS_ACCESS_KEY_ID'],
                'secret' => $_SERVER['AWS_SECRET_ACCESS_KEY'],
                'token' => $_SERVER['AWS_SESSION_TOKEN'] ?? null,
            ],
        ]);

        $publishResponse = $lambda->publishLayerVersion([
            'LayerName' => 'vapor-'.$layer,
            'Description' => $description,
            'Content' => [
                'ZipFile' => file_get_contents(__DIR__."/export/{$layer}.zip"),
            ],
        ]);

        $lambda->addLayerVersionPermission([
            'Action' => 'lambda:GetLayerVersion',
            'LayerName' => 'vapor-'.$layer,
            'Principal' => '*',
            'StatementId' => (string) time(),
            'VersionNumber' => (string) $publishResponse['Version'],
        ]);

        echo '['.$region.']: '.$publishResponse['LayerVersionArn'].PHP_EOL;
    }
}
