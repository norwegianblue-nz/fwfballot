<?php

namespace Drupal\rng\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rng\RegistrationInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Configure registrant settings.
 */
class RegistrationRegistrantEditForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rng_registration_registrant_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RegistrationInterface $registration = NULL) {
    $form['#title'] = $this->t(
      'Hunting party members',
      array('@label' => $registration->label())
    );

    $registrants = $registration->getRegistrants();

    $rows = array();
    foreach ($registrants as $registrant) {
      $row = array();
      $identity = $registrant->getIdentity();
      if ($identity instanceof EntityInterface) {
        $url = $identity->urlInfo();
        $row[] = $this->l($identity->label(), $url);
        //echo 'Here';
      }
      else {
        $row[] = t('<em>Deleted</em>');
      }
        //$fullname = $identity->get('field_physical_address')[0]->given_name . ' ' . $identity->get('field_physical_address')[0]->family_name;
        //$city = $identity->get('field_physical_address')[0]->locality;
        //$city = $identity->field_physical_address[0]->locality;
      $row[] = $registrant->id();
      //$row[] = $fullname;
      //$row[] = $city;
      $rows[] = $row;
    }

    $form['registrants'] = array(
      '#type' => 'table',
      '#header' => array($this->t("Hunter's username"), $this->t('Hunter ID')/*, $this->t("Hunter's name"), $this->t('of')*/),
      '#rows' => $rows,
      '#empty' => $this->t('No identities associated with this registration.'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
