<?php

namespace Drupal\draw_hunting_ballot\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\rng\EventManagerInterface;
use Drupal\Core\Cache\Cache;


/**
 * Provides dynamic tasks.
 */
class LocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The RNG event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * Constructs a LocalTasks object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Drupal\rng\EventManagerInterface $event_manager
   *   The RNG event manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, RouteProviderInterface $route_provider, EventManagerInterface $event_manager) {
    $this->entityManager = $entity_manager;
    $this->routeProvider = $route_provider;
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.manager'),
      $container->get('router.route_provider'),
      $container->get('rng.event_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    /** @var \Drupal\rng\Entity\EventType[] $event_types */
    foreach ($this->eventManager->getEventTypes() as $entity_type => $event_types) {
      $cache_tags = $this->entityManager
        ->getDefinition($entity_type)
        ->getListCacheTags();
      foreach ($event_types as $event_type) {
        $cache_tags = Cache::mergeTags($cache_tags, $event_type->getCacheTags());
      }

      // Only need one set of tasks task per entity type.
      if ($this->routeProvider->getRouteByName("entity.$entity_type.canonical")) {
        $event_default = "rng.event.$entity_type.event.default";

        $this->derivatives["rng.event.$entity_type.event.drawballot"] = array(
          'title' => t('Draw Ballot'),
          'route_name' => "rng.event.$entity_type.drawballot",
          'parent_id' => 'rng.local_tasks:' . $event_default,
          'weight' => -90,
          'cache_tags' => $cache_tags,
        );

        $this->derivatives["rng.event.$entity_type.event.resetballot"] = array(
          'title' => t('Ballot Hard Reset'),
          'route_name' => "rng.event.$entity_type.resetballot",
          'parent_id' => 'rng.local_tasks:' . $event_default,
          'weight' => -91,
          'cache_tags' => $cache_tags,
        );

      }
    }

    foreach ($this->derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
