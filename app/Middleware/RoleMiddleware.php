<?php 

namespace App\Middleware;

class RoleMiddleware extends Middleware
{
	public function __invoke($request, $response, $next)
	{
		if ($this->container->auth->role() == 1) 
		{
			$this->container->flash->addMessage('info', 'You are not authorized to access this page .');
			return $response->withRedirect($this->container->router->pathFor('home'));
		}
 
		$response = $next($request, $response);
		return $response; 

	}
}
	