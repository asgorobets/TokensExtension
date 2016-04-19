<?php

/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */

namespace Behat\TokensExtension\Call;

use Behat\Testwork\Call\Callee;

/**
 * Step token replacement callee interface.
 *
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */
interface TokensReplacement extends Callee
{
    /**
     * Returns token replacement pattern exactly as it was defined.
     *
     * @return string
     */
    public function getPattern();

    /**
     * Represents token replacement as a string.
     * @todo: Is it used?
     * @return string
     */
    public function __toString();
}
