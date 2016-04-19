<?php

namespace Behat\TokensExtension;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

interface StepTokensReplacer
{
    /**
     * @param \Behat\Gherkin\Node\StepNode $step
     * @return StepNode
     */
    public function replaceTokens(Environment $env, FeatureNode $feature, StepNode $step);
}
