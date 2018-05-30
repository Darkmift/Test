<?php 

use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Connection;
use Psr\Container\ContainerInterface as Container;


$container['db2'] = function (Container $container) {
    $settings = $container->get('settings');
    $config = [
        'driver' => 'mysql',
        'host' => $settings['db']['host'],
        'database' => $settings['db']['database'],
        'username' => $settings['db']['username'],
        'password' => $settings['db']['password'],
        'charset' => $settings['db']['charset'],
        'collation' => $settings['db']['collation'],
        'prefix' => '',
    ];
    $factory = new ConnectionFactory(new \Illuminate\Container\Container());
    return $factory->make($config);
};

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
	return $capsule;
};