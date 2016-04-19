<?php

/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */

namespace Behat\TokensExtension\Call;

use Behat\Testwork\Call\RuntimeCallee;

/**
 * Tokens replacement that is created and executed in the runtime.
 *
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */
final class RuntimeTokensReplacement extends RuntimeCallee implements TokensReplacement
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * Initializes tokens replacement.
     *
     * @param string      $pattern
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($pattern, $callable, $description = null)
    {
        $this->pattern = $pattern;

        parent::__construct($callable, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'TokensReplacement ' . $this->getPattern();
    }
}
