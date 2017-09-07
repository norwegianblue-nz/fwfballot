<?php

/**
 * @file
 * Contains \Drupal\draw_hunting_ballot\Form\ResetBallotForm.
 */

namespace Drupal\draw_hunting_ballot\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\rng\EventManagerInterface;
use Drupal\rng\EventMeta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\draw_hunting_ballot\ShuffleObject;
use Drupal\draw_hunting_ballot\ResetHuntingBlockCapacity;

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
/*    $form['help']['#markup'] = $this->t('The current event is: %label', [
      '%label' => $rng_event->label(),
    ]);
*/
    $entity = clone $rng_event;
    $form_state->set('event', $entity);

    $event_meta = $this->eventManager->getMeta($rng_event);
    $form_state->set('currentevent', $event_meta);
    $no_registrations = $event_meta->countRegistrations();
    $event_label = $rng_event->label();

    $form['help']['#markup'] = $this->t("<p>This will prepare the system and make it ready to draw the %label. <br />"
        . "This needs to be done ONLY ONCE PER YEAR, before that year's ballot is drawn.<br />"
        . 'Once the ballot has been drawn, this should NOT be done - all data about the draw will be erased if it is.</p>', [
      '%label' => $rng_event->label(),
    ]);
    $form['table'] = [
      '#type' => 'table',
      '#header' => $this->t(' '),
      '#empty' => $this->t('There are %noentries entries.', ['%noentries' => $no_registrations,]),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Prepare the ' . $event_label),
      '#button_type' => 'primary',
    ];
    /*$form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Please do not press this button'),
      '#button_type' => 'primary',
    ];*/
    //echo (t('Registrations: ' . $no_registrations));
   
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $event = $form_state->get('event');
    $ballotdrawn = $event->get('field_drawn')[0]->value;
    $ballot = $form_state->get('currentevent');
    $registrations = $ballot->getRegistrations();

//    $shuffle = new ShuffleObject($registrations);
//    $drawnballot = $shuffle->shuffleObjects();
    
    /* Reset the capacities to max */
    $cap = new ResetHuntingBlockCapacity();
    $cap->resetCapacities();
    
    /* Reset the 'Drawn' status to 0 */
    $event->field_drawn = '0';
    $event->save();
    

    foreach ($registrations as $entry) {
        //Reset the entry's flags
      $entry->field_allocated_block = NULL;
      $entry->field_allocated_in_draw = '0';
      $entry->field_drawn = '0';
      $entry->save();
    }
    drupal_set_message(t('The ' . $event->label() . ' preparation complete ' ));
  }

}
