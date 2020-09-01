<?php

namespace Drupal\ebook\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
* Controller routines for ebook routes.
*/
class EbookController extends ControllerBase {

  /**
   * Callback for `get.json` API method.
   */
  public function ebook(Request $request)
  {
    return new JsonResponse();
  }
}
