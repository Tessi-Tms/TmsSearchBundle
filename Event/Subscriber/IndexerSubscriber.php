<?php

namespace Tms\Bundle\SearchBundle\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tms\Bundle\SearchBundle\Handler\SearchIndexHandler;
use Tms\Bundle\SearchBundle\IndexableElement\IndexableElementInterface;

class IndexerSubscriber implements EventSubscriberInterface
{
    protected $searchIndexHandler;
    static private $subscribedEvents;
    static private $indexerEvents;

    /**
     *
     * @param SearchIndexHandler $searchIndexHandler
     * @param array $subscribedEvents
     */
    public function __construct(SearchIndexHandler $searchIndexHandler, array $subscribedEvents)
    {
        $this->searchIndexHandler = $searchIndexHandler;
        self::$indexerEvents = array(
            'create' => 'onCreateIndexerPost',
            'update' => 'onUpdateIndexerPost',
            'delete' => 'onDeleteIndexerPre',
        );
        self::$subscribedEvents = array();
        foreach ($subscribedEvents as $action => $eventName) {
            self::$subscribedEvents[$eventName] = array(self::$indexerEvents[$action], 0);
        }
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return self::$subscribedEvents;
    }

    /**
     * @param Object $event
     * @return Object
     */
    public function onCreateIndexerPost($event)
    {
        $this->getSearchIndexHandler()->index($this->getElement($event));
    }

    /**
     * @param Object $event
     * @return Object
     */
    public function onUpdateIndexerPost($event)
    {
        $this->getSearchIndexHandler()->index($this->getElement($event));
    }

    /**
     * @param Object $event
     * @return Object
     */
    public function onDeleteIndexerPre($event)
    {
        $this->getSearchIndexHandler()->unIndex($this->getElement($event));
    }

    /**
     * @return searchIndexHandler
     */
    protected function getSearchIndexHandler()
    {
        return $this->searchIndexHandler;
    }

    /**
     *
     * @param Object $event
     * @return Object
     */
    private function getElement($event)
    {
        $reflectionClass = new \ReflectionClass($event);
        $methods = $reflectionClass->getMethods();
        $getter = $methods[1]->getName();

        return $event->$getter();
    }
}
