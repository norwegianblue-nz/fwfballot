<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Drupal\draw_hunting_ballot;

use Drupal\draw_hunting_ballot\ShuffleObject;
use Drupal\draw_hunting_ballot\ResetHuntingBlockCapacity;
use Drupal\Core\Datetime\DrupalDateTime;


class DrawBallot

{
  private $event;
  private $ballot;
  private $official;
      
  public function __construct($event, $ballot, $official)
  {
    $this->event = $event;
    $this->ballot = $ballot;
    $this->official = $official;
  }
  
  public function ballotdraw()
  {
    $ballotdrawn = $this->event->get('field_drawn')[0]->value;
    $registrations = $this->ballot->getRegistrations();

    $timenow = \Drupal\Core\Datetime\DrupalDateTime::createFromTimestamp(time());
    $drawnorder = '0'; // This may have to be set elsewhere dependant upon waitlist logic
    $shuffle = new ShuffleObject($registrations);
    $drawnballot = $shuffle->shuffleObjects();
    
    /* Reset the capacities to max  
     * if this is first drawing of this ballot (ie not a 'waitlist' draw)
     * (This step not strictly required as done in the preparation stage)     
     */
    if ($ballotdrawn === '0'){
      $cap = new ResetHuntingBlockCapacity();
      $cap->resetCapacities();
      $this->event->field_officialname = $this->official['officialname'];
      $this->event->field_officialposn = $this->official['officialposn'];
      $this->event->field_officialidtype = $this->official['officialidtype'];
      $this->event->field_officialid = $this->official['officialid'];
      //$this->event->field_drawnon = DrupalDateTime::createFromTimestamp(time())->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s'); 
    }
    $this->event->field_drawnon[$ballotdrawn] = DrupalDateTime::createFromTimestamp(time())->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s');
    $ballotdrawn++;
    $this->event->field_drawn = $ballotdrawn;
    $this->event->save();

    foreach ($drawnballot as $entry) {
      $allocated = $entry->get('field_allocated_in_draw')->value;
      $status = $entry->get('field_status')->value;
      if ($allocated === '0' && $status <> 'withdrawn' && $status <> 'cancelled'){
        $partysize = \count($entry->getRegistrants());

        /* Retrieve the Hunting block selection for this entry */
        $huntingblocks = $entry->get('field_block_preference')->referencedEntities();
        foreach ($huntingblocks as $huntingblock) {
          //$huntingblockid = $huntingblock->get('tid')->value;
          $huntingblockcapacity = $huntingblock->get('field_capacity')[0]->value;
          $huntingblockparties = $huntingblock->get('field_parties')[0]->value;
          $huntingblockmaxparties = $huntingblock->get('field_max_parties')[0]->value;
          if ($partysize <= $huntingblockcapacity && $huntingblockparties < $huntingblockmaxparties) {
            $drawnorder++;
            $huntingblockcapacity = $huntingblockcapacity - $partysize;
            $huntingblockparties++;
            $huntingblock->field_capacity = $huntingblockcapacity;
            $huntingblock->field_parties = $huntingblockparties;
            $huntingblock->save();
            /* Set the ballot entry's flags */
            $entry->field_allocated_block = $huntingblock;
            $entry->field_allocated_in_draw = $ballotdrawn;
            $entry->field_drawn = $drawnorder;
            $entry->field_status = 'allo';

            break;
          }
        }
        // Save the ballot entry .
        $entry->save();
      }
    }
    return $drawnorder;
  }
}
