<?php

namespace Drupal\ebook\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Drupal\Core\Entity\EntityTypeManager;

/**
 *
 * @RestResource(
 *   id = "get_request",
 *   label = @Translation("First get request"),
 *   uri_paths = {
 *     "canonical" = "/get-request/{id}",
 *   }
 * )
 */

class EbookResource extends ResourceBase {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ListLicensesResource object.
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
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('logger.factory')->get('dummy'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($id) {
    if (!empty($id)) {
      // Fields array
      $fields = [
        'id',
        'type',
        'uuid',
        'langcode',
        'name',
        'user_id',
        'status',
        'expires_automatically',
        'expiry',
        'licensed_entity',
        'created',
        'default_langcode',
        'field_active',
        'field_datetime',
        'field_education',
        'field_standalone',
        'field_teacher_user',
        'field_terms'
      ];
      $license = $this->entityTypeManager->getStorage('license')->load($id);
      // Check if license available
      if (!empty($license)) {
        // Return good status
        $status = 200;
        foreach ($fields as $field) {
          // Check if fields available and check if fields not empty
          if ($license->hasField($field) && !$license->get($field)->isEmpty()) {
            // Get values
            $response[$field] = $license->get($field)->getString();
          }
          else {
            // Add error message and status "Service is unavailable"
            $status = 404;
            return new JsonResponse($this->t("The field: @field is empty or there is no field in the license", ['@field' => $field]), $status);
          }
        }
      }
      else {
        // Add error message and status "Service is unavailable"
        $status = 404;
        return new JsonResponse($this->t("License with this id does`t exist"), $status);
      }
    }
    return new JsonResponse($response, $status);
  }
}
