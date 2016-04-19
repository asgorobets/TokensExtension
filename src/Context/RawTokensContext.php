<?php

/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */

namespace Behat\TokensExtension\Context;

use Behat\Gherkin\Node\StepNode;
use Behat\TokensExtension\Context\TokensContextInterface;

/**
 * Class RawTokensContext.
 *
 * @package Behat\TokensExtension\Context
 */
class RawTokensContext implements TokensContextInterface
{
    /**
     * Saved replacement tokens.
     *
     * @var array
     */
    private $tokens = [];

    /**
     * {@inheritdoc}
     */
    public function addToken($token, $value)
    {
        $this->tokens[$token] = $value;
    }

    /**
     * @TokensReplacement /\[\[(.+?)\]\]/
     */
    public function replaceStepTokens($value)
    {
        return isset($this->tokens[$value]) ? $this->tokens[$value] : FALSE;
    }
}
