<?php

/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */

namespace Behat\TokensExtension\Call;

use Behat\Behat\Definition\Definition;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Call\EnvironmentCall;
use Behat\Testwork\Environment\Environment;

/**
 * Call extended with tokens replacement information.
 */
final class TokensReplacementCall extends EnvironmentCall
{
    /**
     * @var StepNode
     */
    private $step;

    /**
     * Initializes call.
     *
     * @param Environment    $environment
     * @param TokensReplacement $tokensReplacement
     * @param array          $arguments
     */
    public function __construct(
        Environment $environment,
        StepNode $step,
        TokensReplacement $tokensReplacement,
        array $arguments
    ) {
        parent::__construct($environment, $tokensReplacement, $arguments);

        $this->step = $step;
    }

    /**
     * Returns modified step after tokens replacements.
     *
     * @todo: Is it used?
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }
}
