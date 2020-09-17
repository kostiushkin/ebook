<?php

namespace Drupal\ebook\Plugin\rest\resource;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 * @RestResource(
 *   id = "owner_id",
 *   label = @Translation("Information about user licenses"),
 *   uri_paths = {
 *     "canonical" = "/owner/{user_id}",
 *   }
 * )
 */

class EbookResourceOwnerInformation extends ResourceBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Connect to database
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new EbookResourceOwnerInformation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Database\Connection $database
   *   Connect to database
   */

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityTypeManagerInterface $entity_type_manager,
    Connection $database)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('ebook'),
      $container->get('entity_type.manager'),
      $container->get('database')
    );
  }

  /**
   * * Get information about user licenses by license owner id
   *
   * @param $user_id
   * @return JsonResponse
   */

  public function get($user_id) {
    if (!empty($user_id)) {
      $user = $this->entityTypeManager->getStorage('user')->load($user_id);
      if (!empty($user)) {
        $fields = [
          'id',
          'type',
          'langcode',
          'name',
          'user_id',
          'status',
          'expires_automatically',
          'expiry',
          'licensed_entity',
          'created',
          'default_langcode'
        ];
        $query = $this->database
          ->select('license_field_data', 'lfd')
          ->fields('lfd', $fields)
          ->condition('user_id', $user_id)
          ->execute()
          ->fetchAll();
        return new JsonResponse($query, 200);
      }
      else {
        return new JsonResponse($this->t('No user with this id'));
      }
    }
  }
}
