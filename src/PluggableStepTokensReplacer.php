<?php
/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */
namespace Behat\TokensExtension;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Pluggable tokens replacer service that executes replacement leveraging registered tagged services.
 */
final class PluggableStepTokensReplacer implements StepTokensReplacer
{
    /**
     * @var StepTokensReplacer[]
     */
    private $stepTokensReplacers = array();

    /**
     * Registers new step tokens replacer.
     *
     * @param StepTokensReplacer $stepTokensReplacer
     */
    public function registerStepTokensReplacers(StepTokensReplacer $stepTokensReplacer)
    {
        $this->stepTokensReplacers[] = $stepTokensReplacer;
    }

    /**
     * @inheritdoc
     */
    public function replaceTokens(Environment $env, FeatureNode $feature, StepNode $step)
    {
        // Process registered step tokens replacers.
        foreach ($this->stepTokensReplacers as $stepTokensReplacer) {
            // @todo: Make sure registered tokens replacer claims to support tokens replacement in passed step.
            $step = $stepTokensReplacer->replaceTokens($env, $feature, $step);
        }

        return $step;
    }
}
