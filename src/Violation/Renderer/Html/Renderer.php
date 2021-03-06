<?php

namespace SensioLabs\DeprecationDetector\Violation\Renderer\Html;

use PhpParser\Error;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\Violation\Renderer\MessageHelper\MessageHelper;
use SensioLabs\DeprecationDetector\Violation\Renderer\RendererInterface;
use SensioLabs\DeprecationDetector\Violation\Violation;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Renderer.
 *
 * This Renderer will take a list of violations and will transform them into
 * a HTML page.
 *
 * @author Karl Spies <karl.spies@gmx.net>
 */
class Renderer implements RendererInterface
{
    /**
     * @var MessageHelper
     */
    private $messageHelper;
    /**
     * @var string
     */
    private $outputFilename;
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @param MessageHelper $messageHelper
     * @param Filesystem    $filesystem
     * @param string        $outputFilename
     */
    public function __construct(
        MessageHelper $messageHelper,
        Filesystem $filesystem,
        $outputFilename
    ) {
        $this->messageHelper = $messageHelper;
        $this->fileSystem = $filesystem;
        $this->outputFilename = $outputFilename;
    }

    /**
     * @param Violation[] $violations
     * @param Error[]     $errors
     */
    public function renderViolations(array $violations, array $errors, RuleSet $commonRuleSet, RuleSet $usedRuleSet, RuleSet $notUsedRuleSet)
    {
        $orderedViolations = array();
        // sorting and grouping violations
        foreach ($violations as $violation) {
            $key = $violation->getFile()->getPathname();
            if (!array_key_exists($key, $orderedViolations)) {
                $orderedViolations[$key] = array();
            }

            $fileViolation['message'] = $this->messageHelper->getViolationMessage($violation);
            $fileViolation['line'] = $violation->getLine();
            $fileViolation['comment'] = $violation->getComment();
            $orderedViolations[$key][] = $fileViolation;
        }

        ob_start();
        include __DIR__.'/../../../Resources/templates/htmlTable.phtml';
        $htmlOutput = ob_get_clean();

        $this->fileSystem->mkdir(dirname($this->outputFilename));

        file_put_contents($this->outputFilename, $htmlOutput);
    }
}
