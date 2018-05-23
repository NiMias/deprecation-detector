<?php

namespace SensioLabs\DeprecationDetector\Violation\ViolationChecker;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\TypeGuessing\AncestorResolver;
use SensioLabs\DeprecationDetector\Violation\Violation;

class MethodDefinitionViolationChecker implements ViolationCheckerInterface
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

        foreach ($phpFileInfo->methodDefinitions() as $methodDefinition) {
            $ancestors = $this->ancestorResolver->getClassAncestors($phpFileInfo, $methodDefinition->parentName());

            foreach ($ancestors as $ancestor) {
                if ($ruleSet->hasMethod($methodDefinition->name(), $ancestor)) {
                    $violation = new Violation(
                        $methodDefinition,
                        $phpFileInfo,
                        $ruleSet->getMethod($methodDefinition->name(), $ancestor)->comment()
                    );
                    $violations[] = $violation;

                    $usedRuleSet->merge(new RuleSet([], [], [$ancestor => [$methodDefinition->name() => $ruleSet->getMethod($methodDefinition->name(), $ancestor)]], []));
                    $usedRuleSet->addMethodDeprecationsViolation($methodDefinition->name(), $ancestor, $violation);
                }
            }
        }

        return $violations;
    }
}
