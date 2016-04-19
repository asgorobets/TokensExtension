<?php

/**
 * @author Alexei Gorobet <asgorobets@gmail.com>
 */

namespace Behat\TokensExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\EnvironmentLoader;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TokensExtension.
 *
 * @package Behat\TokensExtension\ServiceContainer
 */
class TokensExtension implements Extension
{
    /*
     * Available services
     */
    const REPOSITORY_ID = 'tokens_replacement.repository';

    /*
     * Available extension points
     */
    const STEP_TOKENS_REPLACER_TAG = 'tokens_replacer.step';

    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes extension.
     *
     * @param null|ServiceProcessor $processor
     */
    public function __construct(ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }


    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadTokensReplacementStepTester($container);
        $this->loadPluggableStepTokensReplacer($container);
        $this->loadDefaultStepTokensReplacers($container);
        $this->loadAnnotationReader($container);
        $this->loadRepository($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processStepTokensReplacers($container);
    }

    /**
     * Loads default step tokens replacers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadDefaultStepTokensReplacers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\TokensExtension\RepositoryStepTokensReplacer', array(
            new Reference(self::REPOSITORY_ID),
            new Reference(CallExtension::CALL_CENTER_ID),
        ));
        $definition->addTag(self::STEP_TOKENS_REPLACER_TAG, array('priority' => 50));
        $container->setDefinition(self::STEP_TOKENS_REPLACER_TAG . '.repository', $definition);
    }

    /**
     * Loads tokens repacement context annotation reader.
     *
     * @param ContainerBuilder $container
     */
    protected function loadAnnotationReader(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\TokensExtension\Context\Annotation\TokensReplacementAnnotationReader');
        $definition->addTag(ContextExtension::ANNOTATION_READER_TAG, array('priority' => 50));
        $container->setDefinition(ContextExtension::ANNOTATION_READER_TAG . '.tokens_replacement', $definition);
    }

    /**
     * Loads tokens replacements repository.
     *
     * @param ContainerBuilder $container
     */
    protected function loadRepository(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\TokensExtension\TokensReplacementsRepository', array(
            new Reference(EnvironmentExtension::MANAGER_ID)
        ));
        $container->setDefinition(self::REPOSITORY_ID, $definition);
    }

    /**
     * Processes all available setp tokens replacers.
     *
     * @param ContainerBuilder $container
     */
    protected function processStepTokensReplacers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::STEP_TOKENS_REPLACER_TAG);
        $definition = $container->getDefinition(self::STEP_TOKENS_REPLACER_TAG);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerStepTokensReplacers', array($reference));
        }
    }

    /**
     * Load pluggable default step tokens replacer.
     *
     * @param ContainerBuilder $container
     */
    private function loadPluggableStepTokensReplacer(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\TokensExtension\PluggableStepTokensReplacer');
        $container->setDefinition(self::STEP_TOKENS_REPLACER_TAG, $definition);
    }

    /**
     * Load tokens replacement step tester wrapper.
     *
     * @param ContainerBuilder $container
     */
    private function loadTokensReplacementStepTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\TokensExtension\TokensReplacementStepTester', array(
            new Reference(TesterExtension::STEP_TESTER_ID),
            new Reference(self::STEP_TOKENS_REPLACER_TAG)
        ));
        $definition->addTag(TesterExtension::STEP_TESTER_WRAPPER_TAG, array('priority' => -999999));
        $container->setDefinition(TesterExtension::STEP_TESTER_WRAPPER_TAG . '.token_replacement', $definition);
    }
}
