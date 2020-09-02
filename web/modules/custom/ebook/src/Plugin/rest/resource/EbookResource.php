<?php

namespace Drupal\ebook\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\JsonResponse;

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
  public function get($id) {
    $status = 200;

    if($id){
      $license = \Drupal::entityTypeManager()->getStorage('license')->load($id);
      $response = [
        'id' => $license->id(),
        'type' => $license->get('type')->getString(),
        'uuid' => $license->get('uuid')->getString(),
        'langcode' => $license->get('langcode')->getString(),
        'name' => $license->get('name')->getString(),
        'user_id' => $license->get('user_id')->getString(),
        'status' => $license->get('status')->getString(),
        'expires_automatically' => $license->get('expires_automatically')->getString(),
        'expiry' => $license->get('expiry')->getString(),
        'licensed_entity' => $license->get('licensed_entity')->getString(),
        'created' => $license->get('created')->getString(),
        'default_langcode' => $license->get('default_langcode')->getString(),
        'field_active' => $license->get('field_active')->getString(),
        'field_datetime' => $license->get('field_datetime')->getString(),
        'field_education' => $license->get('field_education')->getString(),
        'field_standalone' => $license->get('field_standalone')->getString(),
        'field_teacher_user' => $license->get('field_teacher_user')->getValue(),
        'field_terms' => $license->get('field_terms')->getString()
      ];
    }
    return new JsonResponse($response, $status);
  }
}
