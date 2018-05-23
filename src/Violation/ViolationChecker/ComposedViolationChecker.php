<?php

namespace SensioLabs\DeprecationDetector\Violation\ViolationChecker;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;

class ComposedViolationChecker implements ViolationCheckerInterface
{
    /**
     * @var ViolationCheckerInterface[]
     */
    private $violationCheckers;

    /**
     * @param ViolationCheckerInterface[] $violationCheckers
     */
    public function __construct(array $violationCheckers)
    {
        $this->violationCheckers = $violationCheckers;
    }

    /**
     * {@inheritdoc}
     */
    public function check(PhpFileInfo $phpFileInfo, RuleSet $ruleSet, RuleSet $usedRuleSet)
    {
        $violations = array_map(function (ViolationCheckerInterface $checker) use ($phpFileInfo, $ruleSet, $usedRuleSet) {
            try {
                return $checker->check($phpFileInfo, $ruleSet, $usedRuleSet);
            } catch (\Exception $e) {
                # TODO.
                return array();
            }
        }, $this->violationCheckers);

        $result = array();
        array_walk($violations, function ($vio) use (&$result) {
            $result = array_merge($result, $vio);
        });

        return $result;
    }
}
