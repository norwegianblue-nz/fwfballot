<?php

namespace Drupal\draw_hunting_ballot\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\rng\Entity\Registrant;
use Drupal\draw_hunting_ballot\ReleaseHuntingBlock;



/**
 * Class WithdrawEntry.
 */
class WithdrawEntry extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'withdraw_entry';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $current_uid = \Drupal::currentUser()->id();
    $current_user = User::load($current_uid);
    $form_state->set('current_uid', $current_uid);
    $form_state->set('current_user', $current_user);
    
    $registrant_ids = \Drupal::entityQuery('registrant')
      ->condition('identity__target_type', $current_user->getEntityTypeId(), '=')
      ->condition('identity__target_id', $current_user->id(), '=')
      ->execute();
    if ($registrant_ids == NULL){
      drupal_set_message(t($current_user->getUsername() . ' is not a member of any ballot-entry' ),'warning');
    }
    else {
      foreach ($registrant_ids as $registrant_id) {
        $registrant = Registrant::load($registrant_id);
        $registration = $registrant->getRegistration();
      }
      $form_state->set('entry', $registration);
      
      $form['#title'] = $this->t(
        'Are you sure you want to withdraw?',[
            '%party_name' => $registration->get('field_team_name')->value,
            '%regid' => $registration->id(),
          ]
      );
      if ($registration->get('field_status')->value <> 'withdrawn' && $registration->get('field_status')->value <> 'cancelled'){
        $form['help']['#markup'] = $this->t("<p class='withdraw-warning'>You have clicked  'Decline/Withdraw Party'. This will remove <strong>YOUR WHOLE PARTY: %party_name</strong> (entry number %regid) from this year's ballot.<br />"
            . '<strong>This cannot be undone and you assume responsibility for withdrawing your entire party.</strong></p>',[
            '%party_name' => $registration->get('field_team_name')->value,
            '%regid' => $registration->id(),
          ]
            );
        $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => t('Yes I want to withdraw'),
          '#buttontype' => 'primary',
        ];
        $form['help1']['#markup'] = $this->t("<p class='withdraw-confirm'>Clicking 'Yes I want to withdraw', withdraws the <strong>whole party</strong> from this year's ballot.<br />"
            . '<strong>This cannot be undone.</strong></p>'
            . '<p>Choose the "Cancel" button if you are here by mistake.</p>'
            );
        $form['actions1']['submit_cancel'] = [
          '#type' => 'submit',
          '#value' => t('Cancel'),
          '#submit' => array('::submitFormCancel'),
          '#buttontype' => 'cancel',

        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entry = $form_state->get('entry');
    $user = $form_state->get('current_uid');
    
    //Release the block allocationi
    $release = new ReleaseHuntingBlock($entry);
    $release->releaseblock();

    $entry->field_status = 'withdrawn';
    $entry->save();
    \Drupal::logger('draw_hunting_ballot')->notice('Hunter id: %user withdrew Ballot entry %entry.',
    array(
        '%user' => $user,
        '%entry' => $entry->id(),
    ));

    
    $url = \Drupal\Core\Url::fromRoute('draw_hunting_ballot.user_ballot_entry',['user' => $user]);
    $form_state->setRedirectUrl($url);
    

  }
  
  public function submitFormCancel(array &$form, FormStateInterface $form_state) {
    $user = $form_state->get('current_uid');
    $url = \Drupal\Core\Url::fromRoute('draw_hunting_ballot.user_ballot_entry',['user' => $user]);
    $form_state->setRedirectUrl($url);

  }

}
