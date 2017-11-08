<?php

namespace Drupal\draw_hunting_ballot\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\rng\Entity\Registrant;

/**
 * Class UserBallotEntry.
 */
class UserBallotEntry extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_ballot_entry';
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
        //
        $registrant = Registrant::load($registrant_id);
        $registration = $registrant->getRegistration();
  
      }
      $form_state->set('entry', $registration);
      $reg = \Drupal::entityTypeManager()->getViewBuilder('registration')->view($registration, 'userentry');
      $registration_output = render($reg);
      $entrants = $registration->getRegistrants();
      $rows = array();
      foreach ($entrants as $entrant) {
        $row = array();
        $identity = $entrant->getIdentity();
        $hunterdetails = $identity->get('field_physical_address')->getValue();
        $fullname = $hunterdetails[0]['given_name'] . ' ' . $hunterdetails[0]['family_name'];
        $of = $hunterdetails[0]['locality'] . ', ' . $hunterdetails[0]['country_code'];
        $styled_hunterpic_uri = \Drupal\image\Entity\ImageStyle::load('useravatar60')->buildUri($identity->user_picture->entity->getFileUri());
        $hunterpic = array('#theme' => 'image', '#uri' => $styled_hunterpic_uri, '#alt' => 'Hunter Photo', '#style_name' => 'useravatar40');
        $row[] = $fullname;
        $row[] = $of;
        $row[] = \render($hunterpic);
        $rows[] = $row;
      }
    
      
      $form['#title'] = $this->t(
        'Hunting party %party_name (entry number %regid)',[
            '%party_name' => $registration->get('field_team_name')->value,
            '%regid' => $registration->id(),
          ]
      );
      if ($registration->get('field_status')->value === 'allo'){
        $form['message']['#markup'] = $this->t("<p class='accept-msg'>Your party (%party_name) has been allocated a block in this year's ballot.<br />"
            . 'The block allocated and the status are displayed below.<br/ >'
            . 'To accept this block allocation, make a note of your ballot entry number (%regid) and select the<br/>'
            . '"Accept/Confirm allocated block" button.</p>',[
            '%party_name' => $registration->get('field_team_name')->value,
            '%regid' => $registration->id(),
          ]
            );
        $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => t('Accept/Confirm allocated block'),
          '#button_type' => 'primary',
        ];
      }
      if ($registration->get('field_status')->value <> 'withdrawn' && $registration->get('field_status')->value <> 'cancelled'){
        $form['help']['#markup'] = $this->t(
            "<p><br />Should your party no longer wish to hunt in this year's ballot blocks, select the<br />"
            . '"Decline/Withdraw Party" button.</p>'
            . "<p class='withdraw-warning'>NOTE: Selecting 'Decline/Withdraw Party', withdraws the <strong>whole party</strong> from this year's ballot.<br />"
            . '<strong>This cannot be undone.</strong></p>'
            );
        $form['withdraw']['submit_withdraw'] = [
          '#type' => 'submit',
          '#value' => t('Decline/Withdraw Party'),
          '#submit' => array('::submitFormWithdraw'),
        ];
//        $form['help']['#markup'] = $this->t("<p class='withdraw-warning'>Please note: The ability to withdraw is currently disabled.<br />"
//            . 'If you wish to withdraw, please return to this page after 30th October 2017.</p>'
//            );
      }
      $form['hunters'] = array(
        '#type' => 'table',
        '#header' => array(/*$this->t("Hunter's username"), $this->t('Hunter ID'),*/ $this->t("Hunter's name"), $this->t('of')),
        '#rows' => $rows,
        '#empty' => $this->t('No hunters associated with this entry.'),
      );
      $form['UserEntry']['#markup'] = $registration_output;
  
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
    $entry->field_status = 'allocated_confirmed';
    $entry->save();
    \Drupal::logger('draw_hunting_ballot')->notice('Hunter id: %user accepted Ballot entry %entry block allocation.',
    array(
        '%user' => $user,
        '%entry' => $entry->id(),
    ));
    
    $url = \Drupal\Core\Url::fromUserInput('/donation');
    $form_state->setRedirectUrl($url);

  }
  public function submitFormWithdraw(array &$form, FormStateInterface $form_state) {
    $entry = $form_state->get('entry');
    $user = $form_state->get('current_uid');
    
    $url = \Drupal\Core\Url::fromRoute('draw_hunting_ballot.withdraw_entry',['user' => $user]);
    $form_state->setRedirectUrl($url);

  }

}
