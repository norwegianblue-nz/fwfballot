<?php

namespace Drupal\draw_hunting_ballot\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\draw_hunting_ballot\ReleaseHuntingBlock;


/**
 * Form for deleting a registration.
 */
class BallotentryCancelForm extends ContentEntityConfirmFormBase {
  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    if ($this->entity->get('field_status')->value <> 'cancelled'){
      return t('Are you sure you want to cancel this ballot entry?');
    } else {
      return t('This entry is already cancelled');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->urlInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    if ($this->entity->get('field_status')->value <> 'cancelled'){
      return t('Cancel ballot entry');
    } else {
      return t('View ballot');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entry = $this->entity;
    if ($entry->get('field_status')->value <> 'cancelled'){
      $release = new ReleaseHuntingBlock($entry);
      $release->releaseblock();

      $entry->field_status = 'cancelled';
      $entry->save();
    }

    drupal_set_message(t('Ballot entry cancelled.'));

    if ($urlInfo = $entry->urlInfo()) {
      $form_state->setRedirectUrl($urlInfo);
    }
    else {
      $form_state->setRedirect('<front>');
    }
  }

}
