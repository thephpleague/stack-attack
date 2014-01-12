<?php namepsace League\StackAttack\CacheAdapter;

use League\StackAttack\CacheAdapter\CacheAdapterInterface;

class DoctrineAdapter implements CacheAdapterInterface
{
    protected $cache;

    public function __construct(\Doctrine\Common\Cache\Cache $cache)
    {
        $this->cache = $cache;
    }

    public function get($key)
    {
        return $this->cache->fetch($key);
    }

    public function set($key, $value, $minutes)
    {
        return $this->cache->save($key, $value, $minutes);
    }
}