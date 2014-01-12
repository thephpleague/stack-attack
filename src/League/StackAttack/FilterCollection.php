<?php namespace League\StackAttack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\StackAttack\Filters\Blacklist;
use League\StackAttack\Filters\Whitelist;
use League\StackAttack\Throttle;

class FilterCollection
{
    /**
     * Whitelist rules
     *
     * @var array
     */
    protected $whitelist = array();

    /**
     * Blacklist rules
     *
     * @var array
     */
    protected $blacklist = array();

    /**
     * Response
     *
     * @var \Closure
     */
    public $response;

    /**
     * Throttle Object
     *
     * @var Throttle
     */
    protected $throttle;

    /**
     * the Request obj
     *
     * @var Request
     */
    protected $request;

    /**
     * Class constructor
     *
     * @param array        $config The default configs
     * @param CacheAdapter $cache  The cache obj
     */
    public function __construct(array $config, CacheAdapter $cache)
    {
        $this->config = $config;
        $this->cache = $cache;

        $this->initializeLists();
        $this->initializeThrottle();
    }

    /**
     * Setter to bring in the request object
     *
     * @param Request $request Request object
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Check the filters
     *
     * @return boolean "True" means you pass, "False" means go home
     */
    public function checkFilters()
    {
        // Are we on the whitelist?
        if ($this->checkWhitelist()) {
            // you may pass
            return true;

        // Are we on the blacklist?
        } elseif ($this->checkBlacklist()) {
            // you may not pass
            return false;

        // Are we past the throttle limit?
        } elseif ($this->throttle->checkRate($this->request)) {
            // you may not pass
            $this->response = $this->throttle->throttleResponse;
            return false;
        }
    }

    /**
     * Initialize the white and black lists
     *
     * @return void
     */
    protected function initializeLists()
    {
        // if we have whitelist rules, set them up
        if (isset($this->config['whitelist'])) {
            $this->initializeWhitelist();
        }

        // if we have blacklist rules, set them up
        if (isset($this->config['blacklist'])) {
            $this->initializeBlacklist();
        }
    }

    /**
     * Set up the whitelist
     *
     * @return void
     */
    protected function initializeWhitelist()
    {
        foreach ($this->config['whitelist'] as $rule) {
            $this->whitelist[] = new Whitelist($rule['rule']);
        }
    }

    /**
     * Set up the blacklist
     *
     * @return void
     */
    protected function initializeBlacklist()
    {
        foreach ($this->config['blacklist'] as $rule) {
            $this->blacklist[] = new Blacklist($rule['rule']);
        }
    }

    /**
     * Set up the throttle object
     *
     * @return void
     */
    protected function initializeThrottle()
    {
        if (isset($this->config['throttle'])) {
            $this->throttle = new Throttle($this->cache, $this->config['throttle']);
        }
    }

    /**
     * Check request against whitelist
     *
     * @return boolean Is the request on the whitelist?
     */
    public function checkWhitelist()
    {
        // evaluate each whitelist rule.
        // if any rule matches, return true and exit
        // otherwise, return false
        if (! empty($this->whitelist)) {
            foreach ($this->whitelist as $rule) {
                if ($rule->test($this->request)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check request against blacklist
     *
     * @return boolean Is the request on the blacklist?
     */
    public function checkBlacklist()
    {
        // evaluate each blacklist rule.
        // if any rule matches, return true and exit
        // otherwise, return false
        if (! empty($this->blacklist)) {
            foreach ($this->blacklist as $rule) {
                if ($rule->test($this->request)) {
                    $this->setBlacklistResponse();
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set the blacklist response
     *
     * @return void
     */
    protected function setBlacklistResponse()
    {
        // if we have a custom response for blacklist...
        if (isset($this->config['blacklistResponse'])) {
            $this->blacklistResponse = function ($this->request) {
                return new Response($this->config['blacklistResponse']['message'], $this->config['blacklistResponse']['code']);
            };
        } else {
            $this->response = $this->defaultBlacklistResponse();
        }
    }

    /**
     * Set the default response if needed
     *
     * @return void
     */
    protected function defaultBlacklistResponse()
    {
        $this->response = function () {
            return new Response('Blocked', 503);
        }
    }
}
