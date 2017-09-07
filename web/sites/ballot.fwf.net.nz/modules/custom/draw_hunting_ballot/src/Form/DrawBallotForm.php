<?php

/**
 * @file
 * Contains \Drupal\draw_hunting_ballot\Form\DrawBallotForm.
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
class DrawBallotForm extends FormBase {

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
    return 'rng_event_drawballot';
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

    $form['table'] = [
      '#type' => 'table',
      '#header' => $this->t(' '),
      '#empty' => $this->t('There are %noentries entries.', ['%noentries' => $no_registrations,]),
    ];
    $form['help']['#markup'] = $this->t("<p><ol><li>Prepare the system (use the 'Prepare Ballot' tab)</li>"
        . '<li>Go ahead and Draw the %label</li></ol></p>', [
      '%label' => $rng_event->label(),
    ]);
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Draw the ' . $event_label),
      '#button_type' => 'primary',
    ];
    //echo (t('Registrations: ' . $no_registrations));
   
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $drawnorder = '0';
    $event = $form_state->get('event');
    $ballotdrawn = $event->get('field_drawn')[0]->value;
    $ballot = $form_state->get('currentevent');
    $registrations = $ballot->getRegistrations();

    $shuffle = new ShuffleObject($registrations);
    $drawnballot = $shuffle->shuffleObjects();
    
    /* Reset the capacities to max  
     * if this is first drawing of this ballot (ie not a 'waitlist' draw)     */
    if ($ballotdrawn === '0'){
      $cap = new ResetHuntingBlockCapacity();
      $cap->resetCapacities();
    }
    $ballotdrawn++;
    $event->field_drawn = $ballotdrawn;
    $event->save();

    foreach ($drawnballot as $entry) {
      $allocated = $entry->get('field_allocated_in_draw')->value;
      if ($allocated == '0'){
        $hunters = $entry->getRegistrants();
        $partysize = count($hunters);
        //drupal_set_message(t('Entry no: '. $entry->label() . ' - Party size: ' . $partysize)); /* debug info */
        

        /* Retrieve the Hunting block selection for this entry */
        //$choice = $entry->get('field_block_preference')[0]->target_id;
        $huntingblocks = $entry->get('field_block_preference')->referencedEntities();
        //$huntingblock = $entry->get('field_block_preference')[0]->entity;
        foreach ($huntingblocks as $huntingblock) {
          $huntingblockid = $huntingblock->get('tid')->value;
          $huntingblockcapacity = $huntingblock->get('field_capacity')[0]->value;
          if ($partysize <= $huntingblockcapacity) {
            $drawnorder++;
            $huntingblockcapacity = $huntingblockcapacity - $partysize;
            $huntingblock->field_capacity = $huntingblockcapacity;
            $huntingblock->save();
            /* Set the ballot entry's flags */
            $entry->field_allocated_block = $huntingblock;
            $entry->field_allocated_in_draw = $ballotdrawn;
            $entry->field_drawn = $drawnorder;

            //drupal_set_message(t('Entry no: '. $entry->label() . ' - Party size: ' . $partysize . ' Allocated block: '. $huntingblockid . ' Space remaining: ' . $huntingblockcapacity)); /* debug info */
            break;
          }
          //drupal_set_message(t('Capacity: ' . $huntingblockcapacity));
        }
        // Save the ballot entry .
        $entry->save();
      }
    }
//    drupal_set_message(t('Event: ' . $event->label() . ' Registrations: ' . $ballot->countRegistrations()));
    drupal_set_message(t('' . $event->label() . ' drawn: ' . $drawnorder . ' parties allocated hunting blocks'));
  }

}
