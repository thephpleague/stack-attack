<?php

namespace Stack;

include_once '../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ugli\StackAttack\FilterCollection;

$app = new CallableHttpKernel(function (Request $request) {
	return new Response('Hello World!');
});

$filters = (new FilterCollection)
	->blacklist('Block local usage.', function (Request $request) {
		return $request->getClientIp() === '127.0.0.1';
	});

$app = (new Builder)
	->push('Ugli\\StackAttack\\Attack', $filters)
	->resolve($app);

run($app);
