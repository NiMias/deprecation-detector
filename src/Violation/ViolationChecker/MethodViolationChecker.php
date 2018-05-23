<?php

namespace SensioLabs\DeprecationDetector\Violation\ViolationChecker;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\FileInfo\Usage\MethodUsage;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\TypeGuessing\AncestorResolver;
use SensioLabs\DeprecationDetector\Violation\Violation;

class MethodViolationChecker implements ViolationCheckerInterface
{
    /**
     * @var AncestorResolver
     */
    protected $ancestorResolver;

    /**
     * @param AncestorResolver $ancestorResolver
     */
    public function __construct(AncestorResolver $ancestorResolver)
    {
        $this->ancestorResolver = $ancestorResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function check(PhpFileInfo $phpFileInfo, RuleSet $ruleSet, RuleSet $usedRuleSet)
    {
        $violations = array();

        foreach ($phpFileInfo->methodUsages() as $methodUsage) {
            $className = $methodUsage->className();

            if ($ruleSet->hasMethod($methodUsage->name(), $className)) {
                $violation = new Violation(
                    $methodUsage,
                    $phpFileInfo,
                    $ruleSet->getMethod($methodUsage->name(), $className)->comment()
                );
                $violations[] = $violation;

                $usedRuleSet->merge(new RuleSet([], [], [$className => [$methodUsage->name() => $ruleSet->getMethod($methodUsage->name(), $className)]], []));
                $usedRuleSet->addMethodDeprecationsViolation($methodUsage->name(), $className, $violation);
            }

            $ancestors = $this->ancestorResolver->getClassAncestors($phpFileInfo, $methodUsage->className());

            foreach ($ancestors as $ancestor) {
                if ($ruleSet->hasMethod($methodUsage->name(), $ancestor)) {
                    $violation = new Violation(
                        new MethodUsage(
                            $methodUsage->name(),
                            $ancestor,
                            $methodUsage->getLineNumber(),
                            $methodUsage->isStatic()
                        ),
                        $phpFileInfo,
                        $ruleSet->getMethod($methodUsage->name(), $ancestor)->comment()
                    );
                    $violations[] = $violation;

                    $usedRuleSet->merge(new RuleSet([], [], [$ancestor => [$methodUsage->name() => $ruleSet->getMethod($methodUsage->name(), $ancestor)]], []));
                    $usedRuleSet->addMethodDeprecationsViolation($methodUsage->name(), $ancestor, $violation);
                }
            }
        }

        return $violations;
    }
}
