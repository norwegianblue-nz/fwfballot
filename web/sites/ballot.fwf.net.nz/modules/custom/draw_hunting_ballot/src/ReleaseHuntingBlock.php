<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Drupal\draw_hunting_ballot;



class ReleaseHuntingBlock

{
  private $entry;
  //private $ballot;
      
  public function __construct($entry)
  {
    $this->entry = $entry;
    //$this->ballot = $ballot;
  }
  
  public function releaseblock()
  {
    if ($this->entry->get('field_status')->value === 'allo' || $this->entry->get('field_status')->value === 'allocated_confirmed' || $this->entry->get('field_status')->value === 'allocated_paid'){
      $partysize = \count($this->entry->getRegistrants());
      $huntingblocks = $this->entry->get('field_allocated_block')->referencedEntities();
      $huntingblock = $huntingblocks[0];
      $huntingblockcapacity = $huntingblock->get('field_capacity')[0]->value + $partysize;
      $huntingblockparties = $huntingblock->get('field_parties')[0]->value;
      $huntingblockparties--;
      $huntingblock->field_capacity = $huntingblockcapacity;
      $huntingblock->field_parties = $huntingblockparties;
      $huntingblock->save();
    }

    
    return 0;
  }
}
