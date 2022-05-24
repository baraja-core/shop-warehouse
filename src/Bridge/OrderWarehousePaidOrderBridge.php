<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Bridge;


use Baraja\EcommerceStandard\DTO\OrderInterface;
use Baraja\Lock\Lock;
use Baraja\Shop\Warehouse\Entity\WarehouseCapacity;
use Baraja\Shop\Warehouse\WarehouseManager;
use Doctrine\ORM\EntityManagerInterface;

final class OrderWarehousePaidOrderBridge
{
	public function __construct(
		private EntityManagerInterface $entityManager,
		private WarehouseManager $warehouseManager,
	) {
	}


	public function process(OrderInterface $order): void
	{
		Lock::startTransaction('warehouse-order');
		foreach ($order->getItems() as $orderItem) {
			$this->warehouseManager->transformReservationToChangeCapacity(
				WarehouseCapacity::resolveOrderItemHash($order, $orderItem),
			);
		}
		$this->entityManager->flush();
		Lock::stopTransaction('warehouse-order');
	}
}
