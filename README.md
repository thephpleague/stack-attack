# League\StackAttack

StackAttack is a blocking & throttling middleware for [StackPHP](http://stackphp.com), based heavily off of [Rack::Attack](https://github.com/kickstarter/rack-attack) for Ruby.
It currently allows _whitelisting_ and _blacklisting_.

## Install Via Composer

```json
{
    "require": {
        "league/stack-attack": "~1.0@dev"
    }
}
```

## Example

```php
include_once '../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\StackAttack\FilterCollection;
use League\StackAttack\CacheAdapter\DoctrineAdapter;

// Our simple app
$app = new Stack\CallableHttpKernel(function (Request $request) {
    return new Response('Hello World!');
});

// Create a filter collection for the blacklist
$filters = (new FilterCollection)
    ->blacklist('Block dev requests.', function (Request $request) {
        return strpos($request->getPathInfo(), '/dev') === 0;
    });

// Create config array with blacklist response and throttle parameters
$config = [
    'blacklistedResponse' => function (Request $request) {
        // A 503 response makes some bots think they had a successful DDOS
        return new Response('Service Unavailable', 503);
    }
];

$throttleConfig = [
    'cacheKey' => 'api',
    'maxRequests' => 60,
    'interval' => 60
];

$throttleResponse = function (Request $request) {
    return new Response('Rate Limit Exceeded', 403);
};

$throttle = new Throttle($myCache, $throttleResponse, $throttleConfig);

$app = (new Stack\Builder)
    ->push('League\\StackAttack\\Attack', $filters, $throttle, $config)
    ->resolve($app);

Stack\run($app);
```
