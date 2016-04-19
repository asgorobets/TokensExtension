<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\TokensExtension;

use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Step tester replacing tokens in step text.
 */
final class TokensReplacementStepTester implements StepTester
{
    /**
     * @var StepTester
     */
    private $baseTester;

    /**
     * @var StepTokensReplacer
     */
    private $tokensReplacer;

    /**
     * @var StepNode
     */
    private $step;

    /**
     * Initializes tester.
     *
     * @param StepTester $baseTester
     * @param StepTokensReplacer $tokensReplacer
     */
    public function __construct(StepTester $baseTester, StepTokensReplacer $tokensReplacer)
    {
        $this->baseTester = $baseTester;
        $this->tokensReplacer = $tokensReplacer;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        $step = $this->tokensReplacer->replaceTokens($env, $feature, $step);

        if (!$step instanceof StepNode) {
            throw new \RuntimeException('Token replacement failed to return a valid StepNode object');
        }

        $this->step = $step;
        return $this->baseTester->setUp($env, $feature, $this->step, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        return $this->baseTester->test($env, $feature, $this->step, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, StepNode $step, $skip, StepResult $result)
    {
        return $this->baseTester->tearDown($env, $feature, $this->step, $skip, $result);
    }
}
