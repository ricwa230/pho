<?php

namespace pho;

class Runner
{
    public static $formatter;

    private static $suites = [];

    private static $current;

    public static function describe($title, callable $context)
    {
        $previous = self::$current;
        $suite = new Suite($title, $context);
        $suite->parent = $previous;

        // If current is null, this is the root suite for the file
        if (self::$current === null) {
            self::$suites[] = $suite;
        } else {
            self::$current->suites[] = $suite;
        }

        self::$current = $suite;
        $suite->context->__invoke();
        self::$current = $previous;
    }

    public static function it($title, callable $context)
    {
        $spec = new Spec($title, $context, self::$current);
        self::$current->specs[] = $spec;
    }

    public static function before(callable $context)
    {
        $before = new Hook($context);
        self::$current->before = $before;
    }

    public static function after(callable $context)
    {
        $after = new Hook($context);
        self::$current->after = $after;
    }

    public static function beforeEach(callable $context)
    {
        $beforeEach = new Hook($context);
        self::$current->beforeEach = $beforeEach;
    }

    public static function afterEach(callable $context)
    {
        $afterEach = new Hook($context);
        self::$current->afterEach = $afterEach;
    }

    public static function run()
    {
        self::$formatter->beforeRun();

        foreach (self::$suites as $suite) {
            self::runSuite($suite);
        }

        self::$formatter->afterRun();
    }

    private static function runSuite(Suite $suite)
    {
        self::runRunnable($suite->before);
        self::$formatter->beforeSuite($suite);

        // Run nested suites
        foreach ($suite->suites as $nestedSuite) {
            self::runRunnable($suite->beforeEach);
            self::runSuite($nestedSuite);
            self::runRunnable($suite->afterEach);
        }

        // Run the specs
        self::runSpecs($suite);

        self::$formatter->afterSuite($suite);
        self::runRunnable($suite->after);
    }

    private static function runSpecs(Suite $suite)
    {
        foreach ($suite->specs as $spec) {
            self::runRunnable($suite->beforeEach);
            self::$formatter->beforeSpec($spec);

            self::runRunnable($spec);

            self::$formatter->afterSpec($spec);
            self::runRunnable($suite->afterEach);
        }
    }

    private static function runRunnable($runnable)
    {
        if ($runnable instanceof Runnable) {
            $runnable->run();
        }
    }
}
