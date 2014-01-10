<?php namepsace League\StackAttack\CacheAdapter;

use League\StackAttack\CacheAdapter\CacheAdapterInterface;

class IlluminateAdapter implements CacheAdapterInterface
{
    protected $cache;

    public function __construct(\Illuminate\Cache\StoreInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get($key)
    {
        return $this->cache->get($key);
    }

    public function set($key, $value, $minutes)
    {
        return $this->cache->put($key, $value, $minutes);
    }
}