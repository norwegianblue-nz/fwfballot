<?php

namespace Drupal\draw_hunting_ballot\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;

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
  public function manageBallots() {
    $options = ['absolute' => TRUE, 'attributes' => ['class' => 'label']];
    $manageballotslink = Link::createFromRoute('Manage Ballots','view.events.page_1',['node' => '0'],$options);
    $manageentrieslink = Link::createFromRoute('Manage Entries','view.allocated_block.page_1',['node' => '0'],$options);
    $vieworderslink = Link::createFromRoute('View Orders','entity.commerce_order.collection',['node' => '0'],$options);
    return [
      '#type' => 'markup',
      '#markup' => $this->t('<ul class="admin-list">'
          . '<li>%manageballotslink<div class="description">Choose a ballot to manage (change settings, set up messages and draw the ballot)</div></li>'
          . '<li>%manageentrieslink<div class="description">Display ballot entries and choose from the list entries to manage</div></li>'
          . '<li>%vieworderslink<div class="description">View orders</div></li>'
          ,['%manageballotslink' => $manageballotslink->toString(),'%manageentrieslink' => $manageentrieslink->toString(),'%vieworderslink' => $vieworderslink->toString()]
          ),
    ];
  }
}
