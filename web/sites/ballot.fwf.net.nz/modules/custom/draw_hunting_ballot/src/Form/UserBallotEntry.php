<?php

namespace Drupal\draw_hunting_ballot\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\rng\EventManagerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\rng\EventMetaInterface;
use Drupal\rng\RegistrantInterface;
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
    $testing = FALSE;
    
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
        //drupal_set_message(t('The current reg is: ' . $registration->id() ));
  
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
    
      //drupal_set_message(t('The current user is:' . $current_user->getUsername() ),'warning');
      
      $form['#title'] = $this->t(
        'Hunting party %party_name (entry number %regid)',[
            '%party_name' => $registration->get('field_team_name')->value,
            '%regid' => $registration->id(),
          ]
      );
      if ($registration->get('field_status')->value === 'allo'){
        $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => t('Accept/Confirm allocated block'),
          '#button_type' => 'primary',
        ];
      }
      if ($registration->get('field_status')->value <> 'withdrawn' && $registration->get('field_status')->value <> 'cancelled'){
        $form['actions']['submit_withdraw'] = [
          '#type' => 'submit',
          '#value' => t('Decline/Withdraw'),
          '#submit' => array('::submitFormWithdraw'),
        ];
      }
      if ($testing){
        $form['actions']['submit_testing'] = [
          '#type' => 'submit',
          '#value' => t('Testing'),
          '#submit' => array('::submitFormTesting'),
        ];
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
    $entry->field_status = 'allocated_confirmed';
    $entry->save();
    // Display result.
    //foreach ($form_state->getValues() as $key => $value) {
    //  drupal_set_message($key . ': ' . $value);
    //}

  }
  public function submitFormWithdraw(array &$form, FormStateInterface $form_state) {
    $entry = $form_state->get('entry');
    
    //Release the block allocationi
    if ($entry->get('field_status')->value === 'allo' || $entry->get('field_status')->value === 'allocated_confirmed' || $entry->get('field_status')->value === 'allocated_paid'){
      $partysize = \count($entry->getRegistrants());
      $huntingblocks = $entry->get('field_allocated_block')->referencedEntities();
      $huntingblock = $huntingblocks[0];
      $huntingblockcapacity = $huntingblock->get('field_capacity')[0]->value + $partysize;
      $huntingblockparties = $huntingblock->get('field_parties')[0]->value;
      $huntingblockparties--;
      $huntingblock->field_capacity = $huntingblockcapacity;
      $huntingblock->field_parties = $huntingblockparties;
      $huntingblock->save();
    }
//drupal_set_message($partysize);
    // Set the entry status
    $entry->field_status = 'withdrawn';
    $entry->save();

  }
  public function submitFormTesting(array &$form, FormStateInterface $form_state) {
    $entry = $form_state->get('entry');
    $entry->field_status = 'allo';
    $entry->save();
  }

}
