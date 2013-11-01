git <?php

namespace League\StackAttack;

class FilterCollection
{
    protected $whitelist = array();
    protected $blacklist = array();

    public function whitelist($message, \Closure $rule)
    {
        $this->whitelist[] = new Filters\Whitelist($message, $rule);

        return $this;
    }

    public function blacklist($message, \Closure $rule)
    {
        $this->blacklist[] = new Filters\Blacklist($message, $rule);

        return $this;
    }

    public function getWhitelist()
    {
        return $this->whitelist;
    }

    public function getBlacklist()
    {
        return $this->blacklist;
    }
}
