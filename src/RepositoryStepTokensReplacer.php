<?php

/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */

namespace Behat\TokensExtension;

use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Environment\Environment;
use Behat\TokensExtension\Call\TokensReplacement;
use Behat\TokensExtension\Call\TokensReplacementCall;
use Behat\TokensExtension\StepTokensReplacer;

/**
 * Tokens replacer based on discoverable annotations repository.
 *
 * @author Alexei Gorobets <asgorobets@gmail.com>
 */
final class RepositoryStepTokensReplacer implements StepTokensReplacer
{
    /**
     * @var TokensReplacementsRepository
     */
    private $repository;
    /**
     * @var CallCenter
     */
    private $callCenter;

    /**
     * Initializes tokens replacer.
     *
     * @param TokensReplacementsRepository $repository
     * @param CallCenter               $callCenter
     */
    public function __construct(
        TokensReplacementsRepository $repository,
        CallCenter $callCenter
    ) {
        $this->repository = $repository;
        $this->callCenter = $callCenter;
    }
    
    /**
     * {@inheritdoc}
     */
    public function replaceTokens(Environment $environment, FeatureNode $feature, StepNode $step)
    {
        $tokens_replacements = $this->repository->getEnvironmentTokensReplacements($environment);
        $text = $this->replaceTokensText($step->getText(), $tokens_replacements, $step, $environment);
        $arguments = $this->replaceTokensArguments($step->getArguments(), $tokens_replacements, $step, $environment);

        return new StepNode(
            $step->getKeyword(),
            $text,
            $arguments,
            $step->getLine(),
            $step->getKeywordType()
        );
    }

    /**
     * Replace tokens in text with values.
     *
     * @param string $value
     *   Text value to replace the tokens in.
     * @param TokensReplacement[] $tokens_replacements
     *   Tokens replacement callees read.
     * @param StepNode $step
     *   Step node to be modified.
     * @param Environment $environment
     *   Environment from to read.
     * @return string
     *   Processed step text with replaced tokens.
     * @throws null
     */
    protected function replaceTokensText($value, $tokens_replacements, StepNode $step, Environment $environment)
    {
        $newValue = $value;
        foreach ($tokens_replacements as $tokens_replacement) {
            if ($this->isApplicableTokensReplacement($value, $tokens_replacement, $matches)) {
                foreach ($matches['capture_groups'] as $match_index => $match) {
                    // @todo: See how we can cache already replaced results and use the same value for same key.
                    $replacedValue = $this->execute($match, $environment, $step, $tokens_replacement);
                    // Allow step token replacements to return false if they don't know how to deal with the token.
                    if (false !== $replacedValue) {
                        $newValue = str_replace($matches['matched_string'][$match_index], $replacedValue, $newValue);
                    }
                }
            }
        }

        return $newValue;
    }

    /**
     * Replaces tokens in arguments with values.
     *
     * @param ArgumentInterface[] $arguments
     *
     * @return ArgumentInterface[]
     */
    protected function replaceTokensArguments(array $arguments, array $tokens_replacements, StepNode $step, Environment $environment)
    {
        foreach ($arguments as $num => $argument) {
            if ($argument instanceof TableNode) {
                $arguments[$num] = $this->replaceTokensArgumentTable($argument, $tokens_replacements, $step, $environment);
            }
            if ($argument instanceof PyStringNode) {
                $arguments[$num] = $this->replaceTokensArgumentPyString($argument, $tokens_replacements, $step, $environment);
            }
        }

        return $arguments;
    }


    /**
     * Replaces tokens in table with values.
     *
     * @param TableNode $argument
     *
     * @return TableNode
     */
    protected function replaceTokensArgumentTable(TableNode $argument, array $tokens_replacements, StepNode $step, Environment $environment)
    {
        $table = $argument->getTable();
        foreach ($table as $line => $row) {
            foreach (array_keys($row) as $col) {
                $table[$line][$col] = $this->replaceTokensText($table[$line][$col], $tokens_replacements, $step, $environment);
            }
        }

        return new TableNode($table);
    }

    /**
     * Replaces tokens in PyString with values.
     *
     * @param PyStringNode $argument
     *
     * @return PyStringNode
     */
    protected function replaceTokensArgumentPyString(PyStringNode $argument, array $tokens_replacements, StepNode $step, Environment $environment)
    {
        $strings = $argument->getStrings();
        foreach ($strings as $line => $string) {
            $strings[$line] = $this->replaceTokensText($strings[$line], $tokens_replacements, $step, $environment);
        }

        return new PyStringNode($strings, $argument->getLine());
    }

    /**
     * Checks if pattern tokens replacement is applicable.
     *
     * @param string $value
     *   String to replace tokens.
     * @param TokensReplacement $tokensReplacement
     *   Token replacement callee.
     * @param $matches
     *   Array of matches identified after
     * @return bool
     *   TRUE if text matched the pattern, FALSE otherwise.
     */
    private function isApplicableTokensReplacement($value, TokensReplacement $tokensReplacement, &$matches)
    {
        $regex = $tokensReplacement->getPattern();

        if (is_string($value) && preg_match_all($regex, $value, $matches)) {
            // take arguments from capture groups if there are some
            if (count($matches) > 1) {
                // Save full matched string and captured groups.
                $matches = [
                    'matched_string' => $matches[0],
                    'capture_groups' => $matches[1],
                ];
            }

            return true;
        }

        return false;
    }

    /**
     * Executes tokens replacement.
     *
     * @param string $value
     * @param Environment $environment
     * @param StepNode $step
     * @param TokensReplacement $tokensReplacement
     *
     * @return mixed
     *   Return value from the callee callable.
     */
    private function execute($value, Environment $environment, StepNode $step, TokensReplacement $tokensReplacement)
    {
        $call = new TokensReplacementCall(
            $environment,
            $step,
            $tokensReplacement,
            array($value, $step)
        );

        $result = $this->callCenter->makeCall($call);

        if ($result->hasException()) {
            throw $result->getException();
        }

        return $result->getReturn();
    }
}
