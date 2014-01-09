<?php

namespace League\StackAttack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Attack implements HttpKernelInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    private $app;

    /**
     * @var \Closure
     */
    private $blacklistedResponse;

    private $throttleResponse;

    /**
     * @var FilterCollection
     */
    private $filters;

    public function __construct(HttpKernelInterface $app, FilterCollection $filters, array $config = array())
    {
        $this->app = $app;
        $this->filters = $filters;
        $this->config = $config;

        $this->blacklistMesage = ($request->attributes->has('stack.attack.match_message')) ? $request->attributes->get('stack.attack.match_message') : 'Unauthorized';
        $this->throttleMessage = ($request->attributes->has('stack.attack.match_message')) ? $request->attributes->get('stack.attack.match_message') : 'Slow down...';
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        // If this is not a whitelisted request, check the blacklist and the throttle.
        if (! $this->whitelisted($request)) {
            if ($this->blacklisted($request)) {
                return call_user_func($this->blacklistedResponse, $request);
            } else if (! $this->checkThrottle()) {
                return call_user_func($this->throttleResponse, $request);
            }
        }

        return $this->app->handle($request, $type, $catch);
    }

    public function setBlacklistedResponse(\Closure $func = null)
    {
        if ($func !== null) {
            $this->blacklistedResponse = $func;
        } elseif (isset($this->config['blacklistedResponse']) && ($this->config['blacklistedResponse'] instanceof \Closure)) {
            $this->blacklistedResponse = $this->config['blacklistedResponse'];
        } else {
            $this->defaultBlacklistedResponse();
        }
    }

    private function defaultBlacklistedResponse()
    {
        $this->blacklistedResponse = function (Request $request) {
            $message = 'Unauthorized';

            if ($request->attributes->has('stack.attack.match_message')) {
                $message = $request->attributes->get('stack.attack.match_message');
            }

            return new Response($message, 401);
        };
    }

    private function setRepsonses()
    {
        $this->blacklistedResponse = (isset($this->config['blacklistedResponse'])) ?

    }

    private function defaultResponses()
    {

    }

    private function whitelisted(Request $request)
    {
        $whitelist = $this->filters->getWhitelist();
        if (!empty($whitelist)) {
            foreach ($whitelist as $rule) {
                // If the rule passes, then this is a whitelisted request.
                if ($rule->test($request) === true) {
                    return true;
                }
            }
        }

        return false;
    }

    private function blacklisted(Request $request)
    {
        $blacklist = $this->filters->getBlacklist();
        if (!empty($blacklist)) {
            foreach ($blacklist as $rule) {
                // If the rule passes, then this is a blacklisted request.
                if ($rule->test($request)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function checkThrottle(Request $request)
    {
        return true;
    }
}
