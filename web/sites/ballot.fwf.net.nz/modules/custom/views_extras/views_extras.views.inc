<?php

/**
 * @file
 * Contains views_extras\views_extras.views.inc..
 * Provide a custom views field data that isn't tied to any other module. */

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Render\Markup;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\system\ActionConfigEntityInterface;

/**
* Implements hook_views_data().
*/
function views_extras_views_data() {

    $data['views']['table']['group'] = t('Custom Global');
    $data['views']['table']['join'] = [
      // #global is a special flag which allows a table to appear all the time.
      '#global' => [],
    ];


    $data['views']['phpviews_field'] = [
        'title' => t('PHP Field'),
        'help' => t('Add custom PHP to this plugin for inclusion in a view'),
        'field' => [
            'id' => 'phpviews_field',
        ],
    ];
    $data['views']['current_entries'] = [
      'title' => t('Entries for current event only'),
      'filter' => [
        'title' => t('Entries for current event only'),
        'group' => 'Custom',
        'help' => t('Filters out all but entries for current event.'),
        'id' => 'current_entries'
      ],
    ];

    return $data;
}
