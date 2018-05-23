<?php

namespace SensioLabs\DeprecationDetector;

use SensioLabs\DeprecationDetector\Console\Output\DefaultProgressOutput;
use SensioLabs\DeprecationDetector\FileInfo\Usage\MethodUsage;
use SensioLabs\DeprecationDetector\Finder\ParsedPhpFileFinder;
use SensioLabs\DeprecationDetector\RuleSet\Loader\LoaderInterface;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\TypeGuessing\AncestorResolver;
use SensioLabs\DeprecationDetector\Violation\Violation;
use SensioLabs\DeprecationDetector\Violation\ViolationDetector;
use SensioLabs\DeprecationDetector\Violation\Renderer\RendererInterface;

class DeprecationDetector
{
    /**
     * @var RuleSet
     */
    private $preDefinedRuleSet;

    /**
     * @var LoaderInterface
     */
    private $ruleSetLoader;

    /**
     * @var ParsedPhpFileFinder
     */
    private $deprecationFinder;

    /**
     * @var ViolationDetector
     */
    private $violationDetector;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var DefaultProgressOutput
     */
    private $output;

    /**
     * @param RuleSet               $preDefinedRuleSet
     * @param LoaderInterface       $ruleSetLoader
     * @param AncestorResolver      $ancestorResolver
     * @param ParsedPhpFileFinder   $deprecationFinder
     * @param ViolationDetector     $violationDetector
     * @param RendererInterface     $renderer
     * @param DefaultProgressOutput $output
     */
    public function __construct(
        RuleSet $preDefinedRuleSet,
        LoaderInterface $ruleSetLoader,
        AncestorResolver $ancestorResolver,
        ParsedPhpFileFinder $deprecationFinder,
        ViolationDetector $violationDetector,
        RendererInterface $renderer,
        DefaultProgressOutput $output
    ) {
        $this->preDefinedRuleSet = $preDefinedRuleSet;
        $this->ruleSetLoader = $ruleSetLoader;
        $this->ancestorResolver = $ancestorResolver;
        $this->deprecationFinder = $deprecationFinder;
        $this->violationDetector = $violationDetector;
        $this->renderer = $renderer;
        $this->output = $output;
    }

    /**
     * @param string $sourceArg
     * @param string $ruleSetArg
     *
     * @return Violation[]
     *
     * @throws \Exception
     */
    public function checkForDeprecations($sourceArg, $ruleSetArg)
    {
        $this->output->startProgress();

        $this->output->startRuleSetGeneration();
        $ruleSet = $this->ruleSetLoader->loadRuleSet($ruleSetArg);
//        $ruleSet->merge($this->preDefinedRuleSet);
        $this->output->endRuleSetGeneration();

        $this->output->startUsageDetection();

        // TODO: Move to AncestorResolver not hard coded
        $lib = (is_dir($ruleSetArg) ? $ruleSetArg : realpath('vendor'));
        $this->ancestorResolver->setSourcePaths(array(
            $sourceArg,
            $lib,
        ));

        $usedRuleSet = new RuleSet();

        $result = $this->deprecationFinder->parsePhpFiles($sourceArg);
        $violations = $this->violationDetector->getViolations($ruleSet, $result->parsedFiles(), $usedRuleSet);
        $this->output->endUsageDetection();

        $notUsedRuleSet = $this->getNotUsedRuleSet($ruleSet, $usedRuleSet);

        $this->output->startOutputRendering();
        $this->renderer->renderViolations($violations, $result->parserErrors(), $ruleSet, $usedRuleSet, $notUsedRuleSet);
        $this->output->endOutputRendering();

        $this->output->endProgress($result->fileCount(), count($violations));

        return $violations;
    }

    public function getNotUsedRuleSet(RuleSet $ruleSet, RuleSet $usedRuleSet)
    {
        $notUsedClasses = $notUsedMethods = $notUsedFunctions = $notUsedInterfaces = [];

        $notUsedClasses = array_diff_key($ruleSet->classDeprecations(), $usedRuleSet->classDeprecations());
        $notUsedFunctions = array_diff_key($ruleSet->functionDeprecations(), $usedRuleSet->functionDeprecations());

        foreach ($ruleSet->methodDeprecations() as $className => $methodDeprecation) {

            foreach ($methodDeprecation as $methodName => $method) {

                if (!$usedRuleSet->hasMethod($methodName, $className)) {
                    $notUsedMethods[$className][$methodName] = $method;
                }
            }
        }

        $notUsedInterfaces = array_diff_key($ruleSet->interfaceDeprecations(), $usedRuleSet->interfaceDeprecations());

        return new RuleSet($notUsedClasses, $notUsedInterfaces, $notUsedMethods, $notUsedFunctions);
    }
}
