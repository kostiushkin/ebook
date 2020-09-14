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
      $container->get('logger.factory')->get('ebook'),
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
   * Create new license
   *
   * @param $data
   *    Data items
   * @return JsonResponse
   *    Return in JSON Format
   */
  public function post($data) {
    // Use current user after pass authentication to validate access.
    try {
      if (!$this->currentUser->hasPermission('administer site content')) {

        // Display the default access denied page.
        throw new AccessDeniedHttpException('Access Denied.');
      }
    }
    catch (AccessDeniedHttpException $exception) {
      return new JsonResponse($exception->getMessage(), 403);
    }

    // Check if data is empty.
    if (empty($data)) {
      return new JsonResponse($this->t('Error. License is not created'), 406);
    }

    // Validation for required fields.
    if (!array_key_exists('licensed_entity' ,$data) || !array_key_exists('type' ,$data) ) {
      $message = $this->t('Fields licensed_entity and type is required');
      return new JsonResponse($message, 406);
    }
    elseif (!empty($data['licensed_entity']) && !empty($data['type']) && $data['type'] == 'default') {
      $entity = $this->entityTypeManager->getStorage('node')->load($data['licensed_entity']);
      if (!isset($entity)) {
        $message = $this->t('Licencse entity not correct. You need to use a created entities');
        return new JsonResponse($message, 406);
      }
      else {
        try {

          // Get license storage.
          $storage = $this->entityTypeManager->getStorage('license');

          // Create new license.
          $license = $storage->create($data);

          // Save new license.
          $license->save();
          $message = $this->t('New license created successfully');
          return new JsonResponse($message, 200);
        }
        catch (\Exception $exception) {
          return new JsonResponse($exception->getMessage(), 406);
        }
      }
    }
    else {
      $message = $this->t('Licencse entity or license type not correct.');
      return new JsonResponse($message, 406);
    }
  }

  /**
   * @param $id
   * @param $data
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function patch($id, $data){
    $license = $this->entityTypeManager->getStorage('license')->load($id);
    if (!empty($license)){
      $license->set('type', $data['type']);
      $license->set('langcode', $data['langcode']);
      $license->set('status', $data['status']);
      $license->set('expires_automatically', $data['expires_automatically']);
      $license->set('expiry', $data['expiry']);
      $license->set('licensed_entity', $data['licensed_entity']);
      $license->set('created', $data['created']);
      $license->set('default_langcode', $data['default_langcode']);
      $license->set('field_active', $data['field_active']);
      $license->set('field_datetime', $data['field_datetime']);
      $license->set('field_education', $data['field_education']);
      $license->set('field_standalone', $data['field_standalone']);
      $license->set('field_teacher_user', $data['field_teacher_user']);
      $license->set('field_terms', $data['field_terms']);
      $license->save();
      return new JsonResponse($this->t('License updated successfully'), 200);
    }
    else {
      return new JsonResponse($this->t('License with id:@id is don`t exist.', ['@id' => $id]), 406);
    }
  }
}
