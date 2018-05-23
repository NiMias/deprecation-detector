<?php

namespace SensioLabs\DeprecationDetector\Violation\ViolationChecker;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\Violation\Violation;

class InterfaceViolationChecker implements ViolationCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(PhpFileInfo $phpFileInfo, RuleSet $ruleSet, RuleSet $usedRuleSet)
    {
        $violations = array();

        foreach ($phpFileInfo->interfaceUsages() as $interfaceUsageGroup) {
            foreach ($interfaceUsageGroup as $interfaceUsage) {
                if ($ruleSet->hasInterface($interfaceUsage->name())) {
                    $violation = new Violation(
                        $interfaceUsage,
                        $phpFileInfo,
                        $ruleSet->getInterface($interfaceUsage->name())->comment()
                    );
                    $violations[] = $violation;

                    $usedRuleSet->merge(new RuleSet([], [$interfaceUsage->name() => $ruleSet->getInterface($interfaceUsage->name())], [], []));
                    $usedRuleSet->addInterfaceDeprecationsViolation($interfaceUsage->name(), $violation);
                }
            }
        }

        return $violations;
    }
}
