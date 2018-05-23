<?php

namespace SensioLabs\DeprecationDetector\Violation\ViolationChecker;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\Violation\Violation;

class SuperTypeViolationChecker implements ViolationCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(PhpFileInfo $phpFileInfo, RuleSet $ruleSet, RuleSet $usedRuleSet)
    {
        $violations = array();

        foreach ($phpFileInfo->superTypeUsages() as $superTypeUsage) {
            if ($ruleSet->hasClass($superTypeUsage->name())) {
                $violation = new Violation(
                    $superTypeUsage,
                    $phpFileInfo,
                    $ruleSet->getClass($superTypeUsage->name())->comment()
                );
                $violations[] = $violation;

                $usedRuleSet->merge(new RuleSet([$superTypeUsage->name() => $ruleSet->getClass($superTypeUsage->name())]));
                $usedRuleSet->addClassDeprecationsViolation($superTypeUsage->name(), $violation);
            }
        }

        return $violations;
    }
}
