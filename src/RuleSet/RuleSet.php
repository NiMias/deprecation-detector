<?php

namespace SensioLabs\DeprecationDetector\RuleSet;

use SensioLabs\DeprecationDetector\FileInfo\Deprecation\ClassDeprecation;
use SensioLabs\DeprecationDetector\FileInfo\Deprecation\FunctionDeprecation;
use SensioLabs\DeprecationDetector\FileInfo\Deprecation\InterfaceDeprecation;
use SensioLabs\DeprecationDetector\FileInfo\Deprecation\MethodDeprecation;
use SensioLabs\DeprecationDetector\FileInfo\DeprecationCollectionInterface;
use SensioLabs\DeprecationDetector\Violation\Violation;

class RuleSet implements DeprecationCollectionInterface
{
    /**
     * @var array
     */
    private $classDeprecations;

    /**
     * @var array
     */
    private $interfaceDeprecations;

    /**
     * @var array
     */
    private $methodDeprecations;

    /**
     * @var array
     */
    private $functionDeprecations;

    /**
     * @var array
     */
    private $classDeprecationsViolations = [];

    /**
     * @var array
     */
    private $interfaceDeprecationsViolations = [];

    /**
     * @var array
     */
    private $methodDeprecationsViolations = [];

    /**
     * @var array
     */
    private $functionDeprecationsViolations = [];

    /**
     * @param array $classDeprecations
     * @param array $interfaceDeprecations
     * @param array $methodDeprecations
     * @param array $functionDeprecations
     */
    public function __construct(
        array $classDeprecations = array(),
        array $interfaceDeprecations = array(),
        array $methodDeprecations = array(),
        array $functionDeprecations = array()
    ) {
        $this->classDeprecations = $classDeprecations;
        $this->interfaceDeprecations = $interfaceDeprecations;
        $this->methodDeprecations = $methodDeprecations;
        $this->functionDeprecations = $functionDeprecations;
    }

    /**
     * @param DeprecationCollectionInterface $collection
     */
    public function merge(DeprecationCollectionInterface $collection)
    {
        $this->classDeprecations = array_merge(
            $this->classDeprecations(),
            $collection->classDeprecations()
        );
        $this->interfaceDeprecations = array_merge(
            $this->interfaceDeprecations(),
            $collection->interfaceDeprecations()
        );
        $this->methodDeprecations = array_merge(
            $this->methodDeprecations(),
            $collection->methodDeprecations()
        );
        $this->functionDeprecations = array_merge(
            $this->functionDeprecations,
            $collection->functionDeprecations()
        );
    }

    /**
     * @param DeprecationCollectionInterface $collection
     */
    public function merge_recursive(DeprecationCollectionInterface $collection)
    {
        $this->classDeprecations = array_merge_recursive(
            $this->classDeprecations(),
            $collection->classDeprecations()
        );
        $this->interfaceDeprecations = array_merge_recursive(
            $this->interfaceDeprecations(),
            $collection->interfaceDeprecations()
        );
        $this->methodDeprecations = array_merge_recursive(
            $this->methodDeprecations(),
            $collection->methodDeprecations()
        );
        $this->functionDeprecations = array_merge_recursive(
            $this->functionDeprecations,
            $collection->functionDeprecations()
        );
    }

    /**
     * @return ClassDeprecation[]
     */
    public function classDeprecations()
    {
        return $this->classDeprecations;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function hasClass($class)
    {
        return isset($this->classDeprecations[$class]);
    }

    /**
     * @param $class
     *
     * @return ClassDeprecation|null
     */
    public function getClass($class)
    {
        if (!$this->hasClass($class)) {
            return;
        }

        return $this->classDeprecations[$class];
    }

    /**
     * @return InterfaceDeprecation[]
     */
    public function interfaceDeprecations()
    {
        return $this->interfaceDeprecations;
    }

    /**
     * @param string $interface
     *
     * @return bool
     */
    public function hasInterface($interface)
    {
        return isset($this->interfaceDeprecations[$interface]);
    }

    /**
     * @param string $interface
     *
     * @return InterfaceDeprecation|null
     */
    public function getInterface($interface)
    {
        if (!$this->hasInterface($interface)) {
            return;
        }

        return $this->interfaceDeprecations[$interface];
    }

    /**
     * @return MethodDeprecation[]
     */
    public function methodDeprecations()
    {
        return $this->methodDeprecations;
    }

    /**
     * @param string $class
     * @param string $method
     *
     * @return bool
     */
    public function hasMethod($method, $class)
    {
        return isset($this->methodDeprecations[$class][$method]);
    }

    /**
     * @param $method
     * @param $class
     *
     * @return MethodDeprecation|null
     */
    public function getMethod($method, $class)
    {
        if (!$this->hasMethod($method, $class)) {
            return;
        }

        return $this->methodDeprecations[$class][$method];
    }

    /**
     * @return FunctionDeprecation[]
     */
    public function functionDeprecations()
    {
        return $this->functionDeprecations;
    }

    /**
     * @param string $function
     *
     * @return bool
     */
    public function hasFunction($function)
    {
        return isset($this->functionDeprecations[$function]);
    }

    /**
     * @param string $function
     *
     * @return FunctionDeprecation|null
     */
    public function getFunction($function)
    {
        if (!$this->hasFunction($function)) {
            return;
        }

        return $this->functionDeprecations[$function];
    }

    /**
     * @return array
     */
    public function getClassDeprecationsViolations()
    {
        return $this->classDeprecationsViolations;
    }

    /**
     * @return array
     */
    public function getFunctionDeprecationsViolations()
    {
        return $this->functionDeprecationsViolations;
    }

    /**
     * @return array
     */
    public function getInterfaceDeprecationsViolations()
    {
        return $this->interfaceDeprecationsViolations;
    }

    /**
     * @return array
     */
    public function getMethodDeprecationsViolations()
    {
        return $this->methodDeprecationsViolations;
    }

    /**
     * @param array $classDeprecationsViolations
     */
    public function addClassDeprecationsViolation($class, Violation $violation)
    {
        $this->classDeprecationsViolations[$class][] = $violation;
    }

    /**
     * @param array $functionDeprecationsViolations
     */
    public function addFunctionDeprecationsViolation($function, Violation $violation)
    {
        $this->functionDeprecationsViolations[$function][] = $violation;
    }

    /**
     * @param array $interfaceDeprecationsViolations
     */
    public function addInterfaceDeprecationsViolation($interface, Violation $violation)
    {
        $this->interfaceDeprecationsViolations[$interface][] = $violation;
    }

    public function addMethodDeprecationsViolation($method, $class, Violation $violation)
    {
        $this->methodDeprecationsViolations[$class][$method][] = $violation;
    }
}
