<?php

/*
 * Copyright (c) Tyler Sommer
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace TylerSommer\Nice\Benchmark;

use TylerSommer\Nice\Benchmark\ResultPrinter\SimplePrinter;
use TylerSommer\Nice\Benchmark\ResultPruner\StandardDeviationPruner;
use TylerSommer\Nice\Benchmark\Test\CallableTest;

/**
 * A simple operation Benchmark
 */
class Benchmark implements BenchmarkInterface
{
    /**
     * @var int
     */
    protected $iterations;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var array|TestInterface[]
     */
    protected $tests = array();

    /**
     * @var ResultPrinterInterface
     */
    private $resultPrinter;

    /**
     * @var ResultPrunerInterface
     */
    private $resultPruner;

    /**
     * @param int           $iterations    The number of iterations per test
     * @param ResultPrinterInterface $resultPrinter The ResultPrinterInterface to be used
     * @param ResultPrunerInterface  $resultPruner  The ResultPrunerInterface to be used
     */
    public function __construct(
        $iterations = 1000, 
        ResultPrinterInterface $resultPrinter = null,
        ResultPrunerInterface $resultPruner = null
    ) {
        $this->iterations = $iterations;
        $this->resultPrinter = $resultPrinter ?: new SimplePrinter();
        $this->resultPruner = $resultPruner ?: new StandardDeviationPruner();
    }

    /**
     * Set a single parameter
     * 
     * @param $name
     * @param $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }
    
    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Register a test
     *
     * @param string   $name     (Friendly) Name of the test
     * @param callable $callable A valid callable
     */
    public function register($name, $callable)
    {
        $this->tests[] = new CallableTest($name, $callable);
    }

    /**
     * Execute the registered tests and display the results
     */
    public function execute()
    {
        $this->resultPrinter->printIntro($this);
        
        $results = array();
        foreach ($this->tests as $test) {
            $testResults = array();
            for ($i = 0; $i < $this->iterations; $i++) {
                $start = time() + microtime();
                $test->run($this->parameters);
                $testResults[] = round((time() + microtime()) - $start, 10);
            }

            $testResults = $this->resultPruner->prune($testResults);
            
            $this->resultPrinter->printSingleResult($test, $testResults);
            $results[$test->getName()] = $testResults;
        }
        
        $this->resultPrinter->printResultSummary($this, $results);
        
        return $results;
    }

    /**
     * Get all registered Tests
     *
     * @return array|\TylerSommer\Nice\Benchmark\TestInterface[]
     */
    public function getTests()
    {
        return $this->tests;
    }

    /**
     * Gets the Result Pruner
     * 
     * @return \TylerSommer\Nice\Benchmark\ResultPrunerInterface
     */
    public function getResultPruner()
    {
        return $this->resultPruner;
    }

    /**
     * Gets the Result Printer
     * 
     * @return \TylerSommer\Nice\Benchmark\ResultPrinterInterface
     */
    public function getResultPrinter()
    {
        return $this->resultPrinter;
    }

    /**
     * Get the number of iterations the Benchmark should execute each test
     *
     * @return int
     */
    public function getIterations()
    {
        return $this->iterations;
    }

    /**
     * Add a Test to the Benchmark
     *
     * @param TestInterface $test
     */
    public function addTest(TestInterface $test)
    {
        $this->tests[] = $test;
    }

    /**
     * @param int $iterations
     */
    public function setIterations($iterations)
    {
        $this->iterations = $iterations;
    }

    /**
     * @param \TylerSommer\Nice\Benchmark\ResultPrinterInterface $resultPrinter
     */
    public function setResultPrinter($resultPrinter)
    {
        $this->resultPrinter = $resultPrinter;
    }

    /**
     * @param \TylerSommer\Nice\Benchmark\ResultPrunerInterface $resultPruner
     */
    public function setResultPruner($resultPruner)
    {
        $this->resultPruner = $resultPruner;
    }
}