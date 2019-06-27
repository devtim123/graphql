<?php

namespace Drupal\graphql\Plugin\GraphQL\SchemaExtension;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\graphql\Plugin\SchemaExtensionPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class SdlSchemaExtensionPluginBase extends PluginBase implements SchemaExtensionPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler')
    );
  }

  /**
   * SdlSchemaExtensionPluginBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getBaseDefinition() {
    return $this->loadDefinitionFile('base');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getExtensionDefinition() {
    return $this->loadDefinitionFile('extension');
  }

  /**
   * Loads a schema definition file.
   *
   * @param string $type
   *   The type of the definition file to load.
   *
   * @return string|null
   *   The loaded definition file content or NULL if it was empty.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function loadDefinitionFile($type) {
    $id = $this->getPluginId();
    $definition = $this->getPluginDefinition();
    $module = $this->moduleHandler->getModule($definition['provider']);
    $file = "{$module->getPath()}/graphql/{$id}.{$type}.graphqls";

    if (!file_exists($file)) {
      throw new InvalidPluginDefinitionException(sprintf("Missing schema definition file at %s.", $file));
    }

    return file_get_contents($file) ?: NULL;
  }

}
