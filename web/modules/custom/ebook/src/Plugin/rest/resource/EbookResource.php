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
 *     "canonical" = "/get-request",
 *   }
 * )
 */
class EbookResource extends ResourceBase {

  public function get() {
    return new JsonResponse();
  }
}
