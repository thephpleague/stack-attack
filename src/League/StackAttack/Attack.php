<?php namespace League\StackAttack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use League\StackAttack\Throttle;

class Attack implements HttpKernelInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $app;

    /**
     * @var \Closure
     */
    protected $blacklistedResponse;

    /**
     * @var FilterCollection
     */
    protected $filters;

    protected $throttle;

    public function __construct(
        HttpKernelInterface $app,
        FilterCollection $filters,
        $throttle = null,
        array $config = array()
    )
    {
        $this->app = $app;
        $this->filters = $filters;
        $this->config = $config;
        $this->throttle = $throttle;

        $this->setBlacklistedResponse();
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        // If this is not a whitelisted request...
        if (! $this->whitelisted($request)) {

            // ...first check the blacklist...
            if ($this->blacklisted($request)) {
                return call_user_func($this->blacklistedResponse, $request);
            }

            // ... then, if we are using the throttle...
            if (!is_null($this->throttle)) {

                // ...and our request exceeds the rate limit...
                if (! $this->checkThrottle($request)) {
                    return call_user_func($this->throttle->response, $request);
                }
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

    protected function defaultBlacklistedResponse()
    {
        $this->blacklistedResponse = function (Request $request) {
            $message = 'Unauthorized';

            if ($request->attributes->has('stack.attack.match_message')) {
                $message = $request->attributes->get('stack.attack.match_message');
            }

            return new Response($message, 401);
        };
    }

    protected function whitelisted(Request $request)
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

    protected function blacklisted(Request $request)
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

    protected function checkThrottle(Request $request) {
        return $this->throttle->checkRate($request);
    }
}