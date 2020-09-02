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
    if (!empty($id)) {
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
      $license = \Drupal::entityTypeManager()->getStorage('license')->load($id);
      if(!empty($license)) {
        $status = 200;
        foreach($fields as $field) {
          if ($license->hasField($field) && !$license->get($field)->isEmpty()) {
            $response[$field] = $license->get($field)->getString();
          } else {
            $status = 503;
            $response = $this->t("Field with this id does`t exist");
          }
        }
      } else {
        $status = 503;
        $response = $this->t("License with this id does`t exist");
      }
    }
    return new JsonResponse($response, $status);
  }
}
