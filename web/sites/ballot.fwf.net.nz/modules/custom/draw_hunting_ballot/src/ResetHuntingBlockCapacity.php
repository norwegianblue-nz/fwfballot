<?php

namespace Drupal\draw_hunting_ballot;

class ResetHuntingBlockCapacity



{
  
  public function getHuntingBlocks()
  {
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "priority");                        //NOTE: The taxonomy vocab is 'priority'. Poorly named, but no-one see's it.
    $tids = $query->execute();
    $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
    return $terms;
  }
  
  public function resetCapacities()
  {
    foreach ($this->getHuntingBlocks() as $huntingblock) {
      //$huntingblock->field_max_capacity = '6';                // This line can be uncommented to initialise the max capacity of all exisiting hunting blocks
      //$huntingblock->field_max_parties = '2';                // This line can be uncommented to initialise the max parties of all exisiting hunting blocks
      $huntingblockmaxcapacity = $huntingblock->get('field_max_capacity')[0]->value;
      $huntingblock->field_capacity = $huntingblockmaxcapacity;
      $huntingblock->field_parties = '0';
      $huntingblock->save();
    }
  }
}
