<?php namespace League\StackAttack\CacheAdapter;

interface CacheAdapterInterface
{

    public function get($key);
    public function set($key, $value, $time);

}