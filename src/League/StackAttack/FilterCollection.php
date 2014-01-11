<?php namespace League\StackAttack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\StackAttack\Filters\Blacklist;
use League\StackAttack\Filters\Whitelist;

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
     * Blacklist response
     *
     * @var \Closure
     */
    public $blacklistResponse;

    /**
     * Class constructor
     *
     * @param array $config The default configs
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeLists();
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

            // set up the blacklist response, too
            $this->setBlacklistResponse();
        }
    }

    /**
     * Check request against whitelist
     * @param  Request $request Request object
     * @return boolean          Is the request on the whitelist?
     */
    public function checkWhitelist(Request $request)
    {
        // evaluate each whitelist rule.
        // if any rule matches, return true and exit
        // otherwise, return false
        foreach ($this->whitelist as $rule) {
            if ($rule->test($request)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check request against blacklist
     * @param  Request $request Request object
     * @return boolean          Is the request on the blacklist?
     */
    public function checkBlacklist(Request $request)
    {
        // evaluate each blacklist rule.
        // if any rule matches, return true and exit
        // otherwise, return false
        foreach ($this->blacklist as $rule) {
            if ($rule->test($request)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set up the whitelist
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
     * @return void
     */
    protected function initializeBlacklist()
    {
        foreach ($this->config['blacklist'] as $rule) {
            $this->blacklist[] = new Blacklist($rule['rule']);
        }
    }

    /**
     * Set the blacklist response
     * @return void
     */
    protected function setBlacklistResponse()
    {
        // if we have a custom response for blacklist...
        if (isset($this->config['blacklistResponse'])) {
            $this->blacklistResponse = function (Request $request) {
                return new Response($this->config['blacklistResponse']['message'], $this->config['blacklistResponse']['code']);
            };
        } else {
            $this->blacklistResponse = $this->defaultBlacklistResponse();
        }
    }

    /**
     * Set the default response if needed
     * @return void
     */
    protected function defaultBlacklistResponse()
    {
        $this->blacklistResponse = function () {
            return new Response('Blocked', 503);
        }
    }
}
