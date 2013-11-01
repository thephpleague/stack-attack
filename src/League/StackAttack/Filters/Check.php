<?php

namespace League\StackAttack\Filters;

use Symfony\Component\HttpFoundation\Request;

abstract class Check
{
    /**
     * @var  string
     */
    protected $message;

    /**
     * @var \Closure
     */
    protected $rule;

    /**
     * @var string
     */
    protected $type;

    public function __construct($message, \Closure $rule)
    {
        $this->message = $message;
        $this->rule = $rule;
    }

    public function test(Request $request)
    {
        if (call_user_func($this->rule, $request)) {
            $request->attributes->set('stack.attack.match_message', $this->getMessage());
            $request->attributes->set('stack.attack.match_type', $this->getType());

            return true;
        }

        return false;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getType()
    {
        return $this->type;
    }
}
