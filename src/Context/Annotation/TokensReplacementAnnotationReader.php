<?php

/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */

namespace Behat\TokensExtension\Context\Annotation;

use Behat\Behat\Context\Annotation\AnnotationReader;
use Behat\TokensExtension\Call\RuntimeTokensReplacement;
use ReflectionMethod;

/**
 * Step tokens replacement annotation reader.
 *
 * Reads step tokens replacements from a context method annotation.
 *
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */
class TokensReplacementAnnotationReader implements AnnotationReader
{
    /**
     * @var string
     */
    private static $regex = '/^\@tokensreplacement\s+(.+)$/i';

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param string           $contextClass
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return null|RuntimeTokensReplacement
     */
    public function readCallee($contextClass, ReflectionMethod $method, $docLine, $description)
    {
        if (!preg_match(self::$regex, $docLine, $match)) {
            return null;
        }

        $pattern = $match[1];
        $callable = array($contextClass, $method->getName());

        return new RuntimeTokensReplacement($pattern, $callable, $description);
    }
}
