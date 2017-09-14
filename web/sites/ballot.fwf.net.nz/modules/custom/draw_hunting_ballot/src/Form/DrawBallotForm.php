<?php

/**
 * @file
 * Contains \Drupal\draw_hunting_ballot\Form\DrawBallotForm.
 */

namespace Drupal\draw_hunting_ballot\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\rng\EventManagerInterface;
//use Drupal\rng\EventMeta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Render\Element;
use Drupal\draw_hunting_ballot\DrawBallot;
use Drupal\draw_hunting_ballot\PrepBallot;
//use Drupal\draw_hunting_ballot\ShuffleObject;
//use Drupal\draw_hunting_ballot\ResetHuntingBlockCapacity;

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
    $ballotdrawn = $entity->get('field_drawn')[0]->value;

    $form['table'] = [
      '#type' => 'table',
      '#header' => $this->t(' '),
      '#empty' => $this->t('There are %noentries entries.', ['%noentries' => $no_registrations,]),
    ];
    $ballotclose = \Drupal\Core\Datetime\DrupalDateTime::createFromFormat('Y-m-d\Th:i:s',$entity->get('field_ballot_closes')[0]->value);
    $timenow = \Drupal\Core\Datetime\DrupalDateTime::createFromTimestamp(time());
    if (($entity->get('rng_status')[0]->value) === '1' && $timenow < $ballotclose ){
      drupal_set_message(t('The ballot is still accepting entries. The draw cannot be made until the ballot is closed'),'warning');
    } else {
      if ($ballotdrawn == '0'){
        $form['help']['#markup'] = $this->t("<p>System preparation has been done.<br />"
            . 'Go ahead and Draw the %label</p>', [
          '%label' => $rng_event->label(),
        ]);
      } else if ($ballotdrawn == '1'){
        $form['help']['#markup'] = $this->t("<p>The %label has been drawn once.<br />"
            . 'Drawing it again will allocate any spaces now available to parties on the waitlist</p>', [
          '%label' => $rng_event->label(),
        ]);
      } else {
        $form['help']['#markup'] = $this->t("<p>The %label has been drawn %drawn times.<br />"
            . 'Drawing it again will allocate any spaces now available to parties on the waitlist</p>', [
          '%label' => $rng_event->label(),
          '%drawn' => $ballotdrawn,
        ]);
      }
      $form['actions']['submit_draw'] = [
        '#type' => 'submit',
        '#value' => t('Draw the ' . $event_label),
        '#button_type' => 'primary',
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  /*
  public function submitFormPrepare(array &$form, FormStateInterface $form_state) {
    $event = $form_state->get('event');
    $ballot = $form_state->get('currentevent');
    $prep = new PrepBallot($event, $ballot);
    $prepared = $prep->ballotprep();
    drupal_set_message(t('The ' . $event->label() . ' preparation complete ' ));
  }
   */
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $event = $form_state->get('event');
    $ballot = $form_state->get('currentevent');
    $draw = new DrawBallot($event, $ballot);
    $drawnorder = $draw->ballotdraw();

    drupal_set_message(t('' . $event->label() . ' drawn: ' . $drawnorder . ' parties allocated hunting blocks'));
  }

}
