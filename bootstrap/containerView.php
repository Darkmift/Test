<?php 

$container['view'] = function ($container) {
	$view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
		'cache' => false,
	]);

	$view->addExtension(new \Slim\Views\TwigExtension(
		$container->router,
		$container->request->getUri()
	));

	$view->getEnvironment()->addGlobal('auth', [
		'check' => $container->auth->check(),
		'user' => $container->auth->user(),
		'role' => $container->auth->role(),
		'userslist' => $container->DBcontroller->getUsersList(),
		'courseslist' => $container->DBcontroller->getCoursesList(),
		'studentslist' => $container->DBcontroller->getStudentsList(),
		'enrollmentslist' => $container->DBcontroller->getEnrollmentsList(),

	]);
	$view->getEnvironment()->addGlobal('flash', $container->flash);

	return $view;
};