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
    
    $registrant_ids = \Drupal::entityQuery('registrant')
      ->condition('identity__target_type', $current_user->getEntityTypeId(), '=')
      ->condition('identity__target_id', $current_user->id(), '=')
      ->execute();
    foreach ($registrant_ids as $registrant_id) {
      //
      $registrant = Registrant::load($registrant_id);
      $registration = $registrant->getRegistration();
      //drupal_set_message(t('The current reg is: ' . $registration->id() ));

    }
    $reg = \Drupal::entityTypeManager()->getViewBuilder('registration')->view($registration, 'UserEntry');
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

    
    drupal_set_message(t('The current user is:' . $current_user->getUsername() ),'warning');
    
    $form['#title'] = $this->t(
      'Hunting party @party_name',
      array('@party_name' => $registration->get('field_team_name')->value)
    );
    $form['hunters'] = array(
      '#type' => 'table',
      '#header' => array(/*$this->t("Hunter's username"), $this->t('Hunter ID'),*/ $this->t("Hunter's name"), $this->t('of')),
      '#rows' => $rows,
      '#empty' => $this->t('No hunters associated with this entry.'),
    );
    $form['UserEntry']['#markup'] = $registration_output;

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

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
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

}
