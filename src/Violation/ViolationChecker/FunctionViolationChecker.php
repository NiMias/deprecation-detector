<?php

namespace SensioLabs\DeprecationDetector\Violation\ViolationChecker;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\Violation\Violation;

class FunctionViolationChecker implements ViolationCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(PhpFileInfo $phpFileInfo, RuleSet $ruleSet, RuleSet $usedRuleSet)
    {
        $violations = array();

        foreach ($phpFileInfo->getFunctionUsages() as $functionUsage) {
            if ($ruleSet->hasFunction($functionUsage->name())) {
                $violation = new Violation(
                    $functionUsage,
                    $phpFileInfo,
                    $ruleSet->getFunction($functionUsage->name())->comment()
                );
                $violations[] = $violation;

                $usedRuleSet->merge(new RuleSet([], [], [], [$functionUsage->name() => $ruleSet->getFunction($functionUsage->name())]));
                $usedRuleSet->addFunctionDeprecationsViolation($functionUsage->name(), $violation);
            }
        }

        return $violations;
    }
}
