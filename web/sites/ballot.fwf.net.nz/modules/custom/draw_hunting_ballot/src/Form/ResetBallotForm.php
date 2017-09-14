<?php

/**
 * @file
 * Contains \Drupal\draw_hunting_ballot\Form\ResetBallotForm.
 */

namespace Drupal\draw_hunting_ballot\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\rng\EventManagerInterface;
//use Drupal\rng\EventMeta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Render\Element;
use Drupal\draw_hunting_ballot\PrepBallot;
//use Drupal\draw_hunting_ballot\ResetHuntingBlockCapacity;

/**
 * Draw the ballot when user presses the "go" button.
 */
class ResetBallotForm extends FormBase {

  /**
   * The RNG event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * Constructs a new DrawBallotForm object.
   *
   * @param \Drupal\rng\EventManagerInterface $event_manager
   *   The RNG event manager.
   */
  public function __construct(EventManagerInterface $event_manager) {
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('rng.event_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rng_event_resetballot';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, EntityInterface $rng_event = NULL) {
    $entity = clone $rng_event;
    $form_state->set('event', $entity);

    $event_meta = $this->eventManager->getMeta($rng_event);
    $form_state->set('currentevent', $event_meta);
    $no_registrations = $event_meta->countRegistrations();
    $event_label = $rng_event->label();
    drupal_set_message(t('WARNING - This is an emergency procedure only. Ensure you know the consequences of proceeding.'), 'warning');
    $form['help']['#markup'] = $this->t('<p class="warning">WARNING</p>'
        . "<p>This will perform a HARD RESET on the %label. <br />"
        . "This is an emergency action only.<br />"
        . "This action will erase all data re this ballot's draw.</p>", [
      '%label' => $rng_event->label(),
    ]);
    $form['table'] = [
      '#type' => 'table',
      '#header' => $this->t(' '),
      '#empty' => $this->t('There are %noentries entries.', ['%noentries' => $no_registrations,]),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Hard Reset the ' . $event_label),
      '#button_type' => 'primary',
    ];
   
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $event = $form_state->get('event');
    $ballot = $form_state->get('currentevent');
    $prep = new PrepBallot($event, $ballot);
    $prep->ballotprep();
    drupal_set_message(t('The ' . $event->label() . ' Hard Reset complete ' ));
  }

}
