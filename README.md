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

// set up configuration array
$config = [
    'whitelist' => [
        ['rule' => function (Request $request) {
            return strpos($request->getPathInfo(), '/happy') === 0;
        }],
        ['rule' => function (Request $request) {
            return $request->getClientIp() === '1.2.3.4';
        }],
    ],
    'blacklist' => [
        ['rule' => function (Request $request) {
            return strpos($request->getPathInfo(), '/dev') === 0;
        }],
        ['rule' => function (Request $request) {
            return $request->getClientIp() === '4.2.2.2';
        }],
    ],
    'blacklistResponse' => [
        'message' => 'Blocked',
        'code'    => 503
    ],
    'throttle' => [
        'cacheKey'    => 'api',
        'maxRequests' => 60,
        'interval'    => 60,
        'property'    => function (Request $request) {
            return $request->getClientIp();
        },
        'throttleResponse' => [
            'message' => 'Slow Down',
            'code'    => 429
        ]
    ]
];

// If using the throttle, set up the cache instance
$cache = new IlluminateAdapter($myCacheInstance);

// Set up the filter collection, passing in the cache if needed.
$filters = new FilterCollection($config, $cache);

// push the object, with filter collection and go
$app = (new Stack\Builder)
    ->push('League\\StackAttack\\Attack', $filters)
    ->resolve($app);

Stack\run($app);
```
