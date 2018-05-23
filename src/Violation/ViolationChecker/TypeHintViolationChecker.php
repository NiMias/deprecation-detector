<?php

namespace SensioLabs\DeprecationDetector\Violation\ViolationChecker;

use SensioLabs\DeprecationDetector\FileInfo\Usage\ClassUsage;
use SensioLabs\DeprecationDetector\FileInfo\Usage\InterfaceUsage;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\Violation\Violation;

class TypeHintViolationChecker implements ViolationCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(PhpFileInfo $phpFileInfo, RuleSet $ruleSet, RuleSet $usedRuleSet)
    {
        $violations = array();

        foreach ($phpFileInfo->typeHintUsages() as $typeHintUsage) {
            $isClass = $ruleSet->hasClass($typeHintUsage->name());

            if ($isClass || $ruleSet->hasInterface($typeHintUsage->name())) {
                $usage = $isClass ?
                    new ClassUsage($typeHintUsage->name(), $typeHintUsage->getLineNumber()) :
                    new InterfaceUsage($typeHintUsage->name(), '', $typeHintUsage->getLineNumber());

                $comment = $isClass ?
                    $ruleSet->getClass($typeHintUsage->name())->comment() :
                    $ruleSet->getInterface($typeHintUsage->name())->comment();

                $violation = new Violation(
                    $usage,
                    $phpFileInfo,
                    $comment
                );
                $violations[] = $violation;

                if ($isClass) {
                    $usedRuleSet->merge(new RuleSet([$typeHintUsage->name() => $ruleSet->getClass($typeHintUsage->name())]));
                    $usedRuleSet->addClassDeprecationsViolation($typeHintUsage->name(), $violation);
                } else {
                    $usedRuleSet->merge(new RuleSet([], [$typeHintUsage->name() => $ruleSet->getClass($typeHintUsage->name())]));
                    $usedRuleSet->addInterfaceDeprecationsViolation($typeHintUsage->name(), $violation);
                }
            }
        }

        return $violations;
    }
}
