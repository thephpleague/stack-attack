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

$app = new Stack\CallableHttpKernel(function (Request $request) {
    return new Response('Hello World!');
});

$filters = (new FilterCollection)
    ->blacklist('Block dev requests.', function (Request $request) {
        return strpos($request->getPathInfo(), '/dev') === 0;
    });

$config = [
    'blacklistedResponse' => function (Request $request) {
        // A 503 response makes some bots think they had a successful DDOS
        return new Response('Service Unavailable', 503);
    },
    'throttle' => [
        'key' => 'ip',
        'maxRequests' => 60,
        'period' => 60,
        'httpResponse' => function (Request $request) {
            return new Response('Rate Limit Exceeded', 403);
        }
    ]
];

$app = (new Stack\Builder)
    ->push('League\\StackAttack\\Attack', $filters, $config)
    ->resolve($app);

Stack\run($app);
```
