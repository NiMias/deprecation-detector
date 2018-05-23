<?php

namespace SensioLabs\DeprecationDetector\Violation\ViolationChecker;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\Violation\Violation;

class ClassViolationChecker implements ViolationCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(PhpFileInfo $phpFileInfo, RuleSet $ruleSet, RuleSet $usedRuleSet)
    {
        $violations = array();

        foreach ($phpFileInfo->classUsages() as $classUsage) {
            if ($ruleSet->hasClass($classUsage->name())) {
                $violation = new Violation(
                    $classUsage,
                    $phpFileInfo,
                    $ruleSet->getClass($classUsage->name())->comment()
                );
                $violations[] = $violation;

                $usedRuleSet->merge(new RuleSet([$classUsage->name() => $ruleSet->getClass($classUsage->name())]));
                $usedRuleSet->addClassDeprecationsViolation($classUsage->name(), $violation);
            }
        }

        return $violations;
    }
}
