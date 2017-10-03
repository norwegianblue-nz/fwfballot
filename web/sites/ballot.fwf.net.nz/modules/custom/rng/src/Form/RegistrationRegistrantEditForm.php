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
      $hunterdetails = $identity->get('field_physical_address')->getValue();
      $fullname = $hunterdetails[0]['given_name'] . ' ' . $hunterdetails[0]['family_name'];
      $of = $hunterdetails[0]['locality'] . ', ' . $hunterdetails[0]['country_code'];
      $styled_hunterpic_uri = \Drupal\image\Entity\ImageStyle::load('useravatar60')->buildUri($identity->user_picture->entity->getFileUri());
      $hunterpic = array('#theme' => 'image', '#uri' => $styled_hunterpic_uri, '#alt' => 'Hunter Photo', '#style_name' => 'useravatar60');
      $row[] = $fullname;
      $row[] = $of;
      $row[] = \render($hunterpic);
      $rows[] = $row;
    }

    $form['registrants'] = array(
      '#type' => 'table',
      '#header' => array($this->t("Hunter's username")/*, $this->t('Hunter ID')*/, $this->t("Hunter's name"), $this->t('of')),
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
