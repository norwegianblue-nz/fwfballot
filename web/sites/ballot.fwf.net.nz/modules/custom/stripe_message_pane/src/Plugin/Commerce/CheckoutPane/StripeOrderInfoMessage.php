<?php

namespace Drupal\stripe_message_pane\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @CommerceCheckoutPane(
 *  id = "stripe_order_info_message",
 *  label = @Translation("Stripe order info message"),
 *  admin_label = @Translation("Stripe order info message"),
 * )
 */
class StripeOrderInfoMessage extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['message'] = [
      '#markup' => $this->t("<p>FWF accept Visa and MasterCard payments. We use secure payment provided by Stripe and secure encrypted connections using https.</p>"),
    ];
    return $pane_form;
  }

}