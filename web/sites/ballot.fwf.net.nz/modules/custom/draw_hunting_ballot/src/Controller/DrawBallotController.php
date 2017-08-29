<?php

namespace Drupal\draw_hunting_ballot\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DrawBallotController.
 */
class DrawBallotController extends ControllerBase {

  /**
   * Drawballot.
   *
   * @return string
   *   Return Hello string.
   */
  public function drawballot() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: drawballot')
    ];
  }

}
