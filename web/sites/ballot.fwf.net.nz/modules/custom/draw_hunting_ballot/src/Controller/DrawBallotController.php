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
    
  }
  public function manageBallots() {
    $options = ['absolute' => TRUE, 'attributes' => ['class' => 'label']];
    $manageballotslink = Link::createFromRoute('Manage Ballots','view.events.page_1',['node' => '0'],$options);
    $manageentrieslink = Link::createFromRoute('Manage Entries','view.allocated_block.page_1',['node' => '0'],$options);
    $viewpaymentsUrl = \Drupal\Core\Url::fromUserInput('/admin/commerce/orders/paymentreconciliation-1_1');
    $viewpaymentsUrl->setOptions($options);
    $viewpaymentslink = Link::fromTextAndUrl('View Payments',$viewpaymentsUrl);
    $addballotUrl = \Drupal\Core\Url::fromUserInput('/node/add/event');
    $addballotUrl->setOptions($options);
    $createballotlink = Link::fromTextAndUrl('Create New Ballot',$addballotUrl);
    return [
      '#type' => 'markup',
      '#markup' => $this->t('<ul class="admin-list">'
          . '<li>%manageballotslink<div class="description">Choose a ballot to manage (change settings, set up messages and draw the ballot)</div></li>'
          . '<li>%manageentrieslink<div class="description">Display ballot entries and choose from the list entries to manage</div></li>'
          . '<li>%viewpaymentslink<div class="description">View ballot payments</div></li>'
          . '<li>%createballotlink<div class="description">Create ballot entry page for new season</div></li>'
          ,['%manageballotslink' => $manageballotslink->toString(),'%manageentrieslink' => $manageentrieslink->toString(),'%viewpaymentslink' => $viewpaymentslink->toString(),'%createballotlink' => $createballotlink->toString()]
          ),
    ];
  }
}
