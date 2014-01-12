<?php namespace League\StackAttack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\StackAttack\CacheAdapter\CacheAdapterInterface;

class Throttle
{
    /**
     * Default config options
     *
     * @var [type]
     */
    protected $config = [
        'cacheKey'    => 'api',
        'maxRequests' => 60,
        'interval'    => 60,
        'property'    => null,
        'throttleResponse' => [
            'message' => 'Slow Down',
            'code'    => 429
        ]
    ];

    /**
     * Cache Object to use
     *
     * @var \CacheAdapterInterface
     */
    protected $cache;

    /**
     * Response
     *
     * @var \Closure
     */
    public $throttleResponse;

    /**
     * Class constructor
     *
     * @param CacheAdapterInterface $cache  Cache object
     * @param array                 $config Configs
     */
    public function __construct(CacheAdapterInterface $cache, array $config = array()) {

        $this->config = array_merge($this->config, $config);
        $this->cache = $cache;

        // Set default property
        if (is_null($this->config['property'])) {
            $this->config['property'] = function (Request $request) {
                return $request->getClientIp();
            }
        }

        // Set the default response
        $this->defaultResponse();

    }

    /**
     * Check request rate
     *
     * @param  Request $request request object
     * @return bool             Under the rate or not
     */
    public function checkRate(Request $request) {
        // Get our base values
        $prefix = $this->config['cacheKey'];
        $value = call_user_func($request, $this->config['property']);
        $key = sprintf("$prefix:%s", $value);

        // check the cache
        if ($value = $this->cache->get($key)) {
            $value++;
            // Are we under the rate limit?
            return ($value < $this->config['maxRequests']));
        } else {
            $this->cache->set($key, 0, $this->config['interval']);
            return true;
        }
    }

    /**
     * Set the default response
     *
     * @return null
     */
    public function defaultResponse()
    {
        $message = $this->config['throttleResponse']['message'];
        $code = $this->config['throttleResponse']['code'];
        $this->throttleResponse = function() {
            return new Response($message, $code);
        }
    }
}