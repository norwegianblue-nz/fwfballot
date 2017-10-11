<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Drupal\draw_hunting_ballot;

use Drupal\draw_hunting_ballot\ResetHuntingBlockCapacity;


class PrepBallot

{
  private $event;
  private $ballot;
      
  public function __construct($event, $ballot)
  {
    $this->event = $event;
    $this->ballot = $ballot;
  }
  
  public function ballotprep()
  {
    $ballotdrawn = $this->event->get('field_drawn')[0]->value;
    $registrations = $this->ballot->getRegistrations();

    //Reset the capacities to max
    $cap = new ResetHuntingBlockCapacity();
    $cap->resetCapacities();
    
    // Reset the 'Drawn' status to 0 
    $this->event->field_drawn = '0';
    $this->event->field_officialname = null;
    $this->event->field_officialposn = null;
    $this->event->field_officialidtype = null;
    $this->event->field_officialid = null;
    $this->event->field_drawnon = null;
        
    $this->event->save();
    

    foreach ($registrations as $entry) {
      //Reset the entry's flags
      $entry->field_allocated_block = NULL;
      $entry->field_allocated_in_draw = '0';
      $entry->field_drawn = '0';
      $entry->field_status = 'unallocated';
      $entry->save();
    }
/*    $this->event->field_prepared = '0';
    $this->event->save();*/
    return 0;
  }
}
