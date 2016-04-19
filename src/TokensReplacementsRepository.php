<?php
/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */
namespace Behat\TokensExtension;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\TokensExtension\Call\TokensReplacement;

/**
 * Provides token replacement using environment manager.
 */
final class TokensReplacementsRepository
{
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes repository.
     *
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->environmentManager = $environmentManager;
    }

    /**
     * Returns all available definitions for a specific environment.
     *
     * @param Environment $environment
     *
     * @return TokensReplacement[]
     */
    public function getEnvironmentTokensReplacements(Environment $environment)
    {
        return array_filter(
            $this->environmentManager->readEnvironmentCallees($environment),
            function (Callee $callee) {
                return $callee instanceof TokensReplacement;
            }
        );
    }
}
