<?php

namespace SensioLabs\DeprecationDetector\Violation;

use SensioLabs\DeprecationDetector\Console\Output\DefaultProgressOutput;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\Violation\Violation as BaseViolation;
use SensioLabs\DeprecationDetector\Violation\ViolationChecker\ViolationCheckerInterface;
use SensioLabs\DeprecationDetector\Violation\ViolationFilter\ViolationFilterInterface;

class ViolationDetector
{
    /**
     * @var ViolationCheckerInterface
     */
    private $violationChecker;

    /**
     * @var ViolationFilterInterface
     */
    private $violationFilter;

    /**
     * @var DefaultProgressOutput
     */
    private $output;

    /**
     * @param ViolationCheckerInterface $violationChecker
     * @param ViolationFilterInterface  $violationFilter
     */
    public function __construct(
        ViolationCheckerInterface $violationChecker,
        ViolationFilterInterface $violationFilter,
        DefaultProgressOutput $output
    ) {
        $this->violationChecker = $violationChecker;
        $this->violationFilter = $violationFilter;
        $this->output = $output;
    }

    /**
     * @param RuleSet       $ruleSet
     * @param PhpFileInfo[] $files
     *
     * @return BaseViolation[]
     */
    public function getViolations(RuleSet $ruleSet, array $files, RuleSet $usedRuleSet)
    {
        $result = array();

        for ($i=0;isset($files[$i]);$i++) {
            $this->output->echoInfoAboutCheckedFile($i . ": " . $files[$i]->getRelativePathname());
            $unfilteredResult = $this->violationChecker->check($files[$i], $ruleSet, $usedRuleSet);

            foreach ($unfilteredResult as $unfilteredViolation) {

                if (false === $this->violationFilter->isViolationFiltered($unfilteredViolation)) {
                    $result[] = $unfilteredViolation;
                }
            }
        }

        return $result;
    }
}
