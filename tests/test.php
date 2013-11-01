<?php

namespace Stack;

include_once '../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\StackAttack\FilterCollection;

$app = new CallableHttpKernel(function (Request $request) {
    return new Response('Hello World!');
});

$filters = (new FilterCollection)
    ->blacklist('Block dev requests.', function (Request $request) {
        return strpos($request->getPathInfo(), '/dev') === 0;
    });

$app = (new Builder)
    ->push('League\\StackAttack\\Attack', $filters)
    ->resolve($app);

run($app);
