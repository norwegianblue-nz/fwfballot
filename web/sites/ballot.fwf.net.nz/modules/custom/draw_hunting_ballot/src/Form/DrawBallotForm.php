<?php

/**
 * @file
 * Contains \Drupal\draw_hunting_ballot\Form\DrawBallotForm.
 */

namespace Drupal\draw_hunting_ballot\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\rng\EventManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\draw_hunting_ballot\DrawBallot;
use Drupal\draw_hunting_ballot\PrepBallot;
use Drupal\Core\Datetime\DrupalDateTime;

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
    $ballotclose = DrupalDateTime::createFromFormat('Y-m-d\Th:i:s',$entity->get('field_ballot_closes')[0]->value);
    $timenow = DrupalDateTime::createFromTimestamp(time());
    if (($entity->get('rng_status')[0]->value) === '1' && $timenow < $ballotclose ){
      drupal_set_message(t('The ballot is still accepting entries. The draw cannot be made until the ballot is closed'),'warning');
    } else {
      if ($ballotdrawn === '0'){
        $form['help']['#markup'] = $this->t("<p>The ballot is closed and the draw may take place.<br />"
            . 'The designated official should fill in the following details and then Draw the %label</p>', [
          '%label' => $rng_event->label(),
        ]);
        $form['official_name'] = array(
          '#type' => 'textfield',
          '#title' => $this->t("Official's name"),
          '#description' => $this->t('Full name of the official making the draw'),
          '#required' => true,
        );
        $form['official_posn'] = array(
          '#type' => 'textfield',
          '#title' => $this->t("Official's position"),
          '#description' => $this->t('Official position (e.g. Police Officer, JP) of person making the draw'),
          '#required' => true,
        );
        $form['official_idtype'] = array(
          '#type' => 'textfield',
          '#title' => $this->t("Official's identification type (e.g. 'Police ID', 'Drivers License')"),
          '#description' => $this->t('Identification type used'),
          '#required' => true,
        );
        $form['official_id'] = array(
          '#type' => 'textfield',
          '#title' => $this->t("Official's identification (e.g. Police ID #, Driver's License #)"),
          '#title_display' => 'before',
          '#description' => $this->t('Provide the unique value of your identification - eg the Drivers License Number found on your license'),
          '#required' => true,
        );
      } else if ($ballotdrawn === '1'){
        $form['help']['#markup'] = $this->t("<p>The %label has been drawn once.<br />"
            . 'Drawing it again will allocate any spaces now available to parties on the waitlist</p>', [
          '%label' => $rng_event->label(),
        ]);
      } else if ($ballotdrawn === '2'){
        $form['help']['#markup'] = $this->t("<p>The %label has been drawn twice.<br />"
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
    $official = array(
      'officialname' => $form_state->getValue('official_name'),
      'officialposn' => $form_state->getValue('official_posn'),
      'officialidtype' => $form_state->getValue('official_idtype'),
      'officialid' => $form_state->getValue('official_id'),
    );
    $draw = new DrawBallot($event, $ballot, $official);
    $drawnorder = $draw->ballotdraw();

    drupal_set_message(t('' . $event->label() . ' drawn: ' . $drawnorder . ' parties allocated hunting blocks'));
  }

}
