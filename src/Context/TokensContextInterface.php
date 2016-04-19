<?php

/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */

namespace Behat\TokensExtension\Context;

use Behat\Behat\Context\Context;

/**
 * Interface TokensContextInterface.
 *
 * @package Behat\TokensExtension\Context
 */
interface TokensContextInterface extends Context
{
    /**
     * Set parameters from behat.yml.
     *
     * @param array $parameters
     *   An array of parameters from configuration file.
     */
    public function addToken($token, $value);
}
