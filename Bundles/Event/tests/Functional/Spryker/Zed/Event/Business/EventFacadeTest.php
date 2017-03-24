<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Functional\Spryker\Zed\Event\Business;

use Codeception\TestCase\Test;
use Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer;
use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Generated\Shared\Transfer\QueueSendMessageTransfer;
use Spryker\Shared\Event\EventConstants;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use Spryker\Zed\Event\Business\EventBusinessFactory;
use Spryker\Zed\Event\Business\EventFacade;
use Spryker\Zed\Event\Dependency\Client\EventToQueueInterface;
use Spryker\Zed\Event\Dependency\EventCollection;
use Spryker\Zed\Event\Dependency\EventCollectionInterface;
use Spryker\Zed\Event\Dependency\Plugin\EventListenerInterface;
use Spryker\Zed\Event\EventDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @group Functional
 * @group Spryker
 * @group Zed
 * @group Event
 * @group Business
 * @group EventFacadeTest
 */
class EventFacadeTest extends Test
{

    const TEST_EVENT_NAME = 'test.event';

    /**
     * @return void
     */
    public function testTriggerShouldHandleGivenListener()
    {
        $eventFacade = $this->createEventFacade();
        $transferObject = $this->createTransferObjectMock();

        $eventCollection = $this->createEventListenerCollection();

        $eventListenerMock = $this->createEventListenerMock();
        $eventListenerMock->expects($this->once())
            ->method('handle')
            ->with($transferObject);

        $eventCollection->addListener(static::TEST_EVENT_NAME, $eventListenerMock);

        $eventBusinessFactory = $this->createEventBusinessFactory(null, $eventCollection);

        $eventFacade->setFactory($eventBusinessFactory);
        $eventFacade->trigger(static::TEST_EVENT_NAME, $transferObject);
    }

    /**
     * @return void
     */
    public function testTriggerWhenQueueUsedShouldEnqueueListener()
    {
        $eventFacade = $this->createEventFacade();
        $transferObject = $this->createTransferObjectMock();

        $eventCollection = $this->createEventListenerCollection();
        $eventListenerMock = $this->createEventListenerMock();

        $eventCollection->addListenerQueued(static::TEST_EVENT_NAME, $eventListenerMock);

        $mockedQueueClient = $this->createQueueClientMock();

        $mockedQueueClient->expects($this->once())
            ->method('sendMessage')
            ->with(
                EventConstants::EVENT_QUEUE,
                $this->containsOnlyInstancesOf(QueueSendMessageTransfer::class)
            );

        $eventBusinessFactory = $this->createEventBusinessFactory($mockedQueueClient, $eventCollection);

        $eventFacade->setFactory($eventBusinessFactory);
        $eventFacade->trigger(static::TEST_EVENT_NAME, $transferObject);
    }

    /**
     * @return void
     */
    public function testProcessEnqueuedMessagesShouldHandleProvidedEvents()
    {
        $eventFacade = $this->createEventFacade();
        $transferObject = $this->createTransferObjectMock();

        $eventCollection = $this->createEventListenerCollection();
        $eventListenerMock = $this->createEventListenerMock();

        $eventCollection->addListenerQueued(static::TEST_EVENT_NAME, $eventListenerMock);

        $queueReceivedMessageTransfer = $this->createQueueReceiveMessageTransfer($eventListenerMock, $transferObject);

        $messages[] = $queueReceivedMessageTransfer;

        $processedMessages = $eventFacade->processEnqueuedMessages($messages);

        $processedQueueReceivedMessageTransfer = $processedMessages[0];

        $this->assertTrue($processedQueueReceivedMessageTransfer->getAcknowledge());
    }

    /**
     * @return void
     */
    public function testProcessEnqueuedMessagesShouldMarkAsFailedWhenDataIsMissing()
    {
        $eventFacade = $this->createEventFacade();

        $eventCollection = $this->createEventListenerCollection();
        $eventListenerMock = $this->createEventListenerMock();

        $eventCollection->addListenerQueued(static::TEST_EVENT_NAME, $eventListenerMock);

        $queueReceivedMessageTransfer = $this->createQueueReceiveMessageTransfer();

        $messages[] = $queueReceivedMessageTransfer;

        $processedMessages = $eventFacade->processEnqueuedMessages($messages);

        $processedQueueReceivedMessageTransfer = $processedMessages[0];

        $this->assertFalse($processedQueueReceivedMessageTransfer->getAcknowledge());
        $this->assertTrue($processedQueueReceivedMessageTransfer->getReject());
        $this->assertTrue($processedQueueReceivedMessageTransfer->getHasError());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Event\Dependency\Client\EventToQueueInterface
     */
    protected function createQueueClientMock()
    {
        return $this->getMockBuilder(EventToQueueInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Event\Dependency\Plugin\EventListenerInterface
     */
    protected function createEventListenerMock()
    {
        return $this->getMockBuilder(EventListenerInterface::class)
            ->getMock();
    }

    /**
     * @return \Spryker\Zed\Event\Dependency\EventCollectionInterface
     */
    protected function createEventListenerCollection()
    {
        return new EventCollection();
    }

    /**
     * @return \Spryker\Zed\Event\Business\EventFacade
     */
    protected function createEventFacade()
    {
        return new EventFacade();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    protected function createTransferObjectMock()
    {
        return $this->getMockBuilder(TransferInterface::class)
           ->getMock();
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\Client\EventToQueueInterface|null $queueClientMock
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface|null $eventCollection
     * @param array|\Spryker\Zed\Event\Dependency\Plugin\EventSubscriberInterface[] $eventSubscribers
     *
     * @return \Spryker\Zed\Event\Business\EventBusinessFactory
     */
    protected function createEventBusinessFactory(
        EventToQueueInterface $queueClientMock = null,
        EventCollectionInterface $eventCollection = null,
        array $eventSubscribers = []
    ) {

        if ($queueClientMock === null) {
            $queueClientMock = $this->createQueueClientMock();
        }

        $eventDependencyProvider = new EventDependencyProvider();

        $container = new Container();

        $businessLayerDependencies = $eventDependencyProvider->provideBusinessLayerDependencies($container);

        $container[EventDependencyProvider::CLIENT_QUEUE] = function (Container $container) use ($queueClientMock) {
            return $queueClientMock;
        };

        $container[EventDependencyProvider::EVENT_LISTENERS] = function (Container $container) use ($eventCollection) {
            return $eventCollection;
        };

        $container[EventDependencyProvider::EVENT_SUBSCRIBERS] = function (Container $container) use ($eventSubscribers) {
            return $eventSubscribers;
        };

        $eventBusinessFactory = new EventBusinessFactory();
        $eventBusinessFactory->setContainer($businessLayerDependencies);

        return $eventBusinessFactory;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\Plugin\EventListenerInterface|null $eventListenerMock
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface|null $transferObject
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer
     */
    protected function createQueueReceiveMessageTransfer(
        EventListenerInterface $eventListenerMock = null,
        TransferInterface $transferObject = null
    ) {

        $message = [
            EventQueueSendMessageBodyTransfer::LISTENER_CLASS_NAME => ($eventListenerMock) ? get_class($eventListenerMock) : null,
            EventQueueSendMessageBodyTransfer::TRANSFER_CLASS_NAME => ($transferObject) ? get_class($transferObject) : null,
            EventQueueSendMessageBodyTransfer::TRANSFER_DATA => ['1', '2', '3'],
            EventQueueSendMessageBodyTransfer::EVENT_NAME => static::TEST_EVENT_NAME,
        ];

        $queueMessageTransfer = new QueueSendMessageTransfer();
        $queueMessageTransfer->setBody(json_encode($message));

        $queueReceivedMessageTransfer = new QueueReceiveMessageTransfer();
        $queueReceivedMessageTransfer->setQueueMessage($queueMessageTransfer);
        return $queueReceivedMessageTransfer;
    }

}
