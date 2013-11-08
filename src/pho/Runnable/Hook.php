<?php

namespace pho\Runnable;

class Hook extends Runnable
{
    /**
     * Constructs a hook object, to be associated with any of a suite's hooks,
     * ie: before, after, beforeEach, and afterEach.
     *
     * @param \Closure $closure The closure to invoke when the hook is called
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }
}
