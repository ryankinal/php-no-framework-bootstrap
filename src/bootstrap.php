<?php
namespace Example;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../config.php';

$whoops = new \Whoops\Run;
if (ENVIRONMENT !== 'production') {
	$whoops->pushHandler(new \Whoops\handler\PrettyPageHandler);
} else {
	$whoops->pushHandler(function($e) {
		echo 'Friendly error page and send email to the developer';
	});
}
$whoops->register();

// HTTP request and response
$request = Request::createFromGlobals();
$response = new Response();

// JSON request format
if ($request->headers->get('Content-Type') === 'application/json') {
	$body = json_decode($request->getContent(), true);

	if ($body)
	{
		$request->request->replace($body);		
	}
}

// Routing
$routeDefinitionCallback = function(\FastRoute\RouteCollector $r) {
	$routes = include(__DIR__.'/routes.php');
	foreach ($routes as $route) {
		$r->addRoute($route[0], $route[1], $route[2]);
	}
};

$dispatcher = \FastRoute\simpleDispatcher($routeDefinitionCallback);

$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

switch($routeInfo[0]) {
	case \FastRoute\Dispatcher::NOT_FOUND:
		$response->setContent('404 - Page not found');
		$response->setStatusCode(404);
		break;
	case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
		$response->setContent('405 - Method not  allowed');
		$response->setStatusCode(405);
		break;
	case \FastRoute\Dispatcher::FOUND:
		$className = $routeInfo[1][0];
		$function = $routeInfo[1][1];
		$vars = $routeInfo[2];
		$object = new $className($request, $response);
		$object->$function($vars);
		break; 
}

$response->headers->set('Content-Type', 'application/json');

$response->send();
?>