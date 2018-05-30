<?php
$container = $app->getContainer();

$container['auth'] = function ($container) {

	return new \App\Auth\Auth;
};

$container['flash'] = function($container)
{
	return new \Slim\Flash\messages;
};

require __DIR__.'/containerView.php';

$container['DBcontroller'] = function ($container) {
	return new App\Controllers\DBcontroller($container);
};
$container['validator'] = function ($container) {
	return new App\Validation\Validator;
};

$container['ImageValidator'] = function ($container) {
    return new App\Validation\ImageValidator($container);
};

$container['csrf'] = function ($container) {

	return new \Slim\Csrf\Guard;
};


$container['directory_IMG_students'] = __DIR__.'\..\public\images\students';

$container['directory_IMG_admins'] = __DIR__.'\..\public\images\admins';

$container['directory_IMG_courses'] = __DIR__.'\..\public\images\courses';
