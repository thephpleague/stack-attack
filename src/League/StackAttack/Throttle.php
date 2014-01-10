<?php namespace League\StackAttack;

use Symfony\Component\HttpFoundation\Request;
use League\StackAttack\CacheAdapter\CacheAdapterInterface;

class Throttle
{
    protected $config = [
        'cacheKey' => 'api',
        'maxRequests' => 60,
        'interval' => 60
    ];

    protected $cache;

    public $response;

    public function __construct(CacheAdapterInterface $cache, \Closure $response, array $config = array()) {

        $this->config = array_merge($this->config, $config);
        $this->cache = $cache;
        $this->response = $response;

    }

    public function checkRate(Request $request) {
        $prefix = $this->config['cacheKey'];
        $key = sprintf("$prefix:%s", $request->getClientIp());

        if ($value = $this->cache->get($key)) {
            $value++;
            // Are we under the rate limit?
            return ($value < $this->config['maxRequests']));
        } else {
            $this->cache->set($key, 0, $this->config['interval']);
            return true;
        }
    }
}