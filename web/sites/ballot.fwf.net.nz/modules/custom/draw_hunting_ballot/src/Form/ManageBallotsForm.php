<?php

/**
 * @file
 * Contains \Drupal\draw_hunting_ballot\Form\ManageBallotForm.
 */

namespace Drupal\draw_hunting_ballot\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ManageBallotForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'manage_ballots';
  }
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return ['draw_hunting_ballot.manageballots'];  
  }  
  /**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config('draw_hunting_ballot.manageballots');  

    $form['welcome_message'] = [  
      '#type' => 'textarea',  
      '#title' => $this->t('Welcome message'),  
      '#description' => $this->t('Welcome message display to users when they login'),  
      '#default_value' => $config->get('welcome_message'),  
    ];  

    return parent::buildForm($form, $form_state);  
  }  
  /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    parent::submitForm($form, $form_state);  

    $this->config('draw_hunting_ballot.manageballots')  
      ->set('welcome_message', $form_state->getValue('welcome_message'))  
      ->save();  
  }  

}
