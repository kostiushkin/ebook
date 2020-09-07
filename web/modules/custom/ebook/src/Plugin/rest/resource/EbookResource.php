<?php

namespace Drupal\ebook\Plugin\rest\resource;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *
 * @RestResource(
 *   id = "get_request",
 *   label = @Translation("First get request"),
 *   uri_paths = {
 *     "canonical" = "/get-request/{id}",
 *     "create" = "/rest/post"
 *   }
 * )
 */

class EbookResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EbookResource object.
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
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   *
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
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
      $container->get('current_user'),
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

  /**
   * Responds to POST requests.
   *
   * @param $data
   *    Data items
   * @return JsonResponse
   *    Return in JSON Format
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function post($data) {

    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('administer site content')) {
      // Display the default access denied page.
      throw new AccessDeniedHttpException('Access Denied.');
    }

    // Check if data is empty
    if (empty($data)) {
      return new JsonResponse($this->t("Error. License is not created"), 404);
    }

    // Validation for required fields
    if(array_key_exists("licensed_entity" ,$data) && array_key_exists("type" ,$data) ) {
      // Get license storage
      $storage = $this->entityTypeManager->getStorage('license');
      // Create new license
      $license = $storage->create($data);
      // Save new license
      $license->save();
      $message = $this->t("New license created successfully");
      return new JsonResponse($message, 200);
    }
    else {
      $message = $this->t("Fields licensed_entity and type is required");
      return new JsonResponse($message, 404);
    }
  }
}
