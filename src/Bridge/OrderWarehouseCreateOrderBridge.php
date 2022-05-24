<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Bridge;


use Baraja\EcommerceStandard\DTO\OrderInterface;
use Baraja\Shop\Order\CreatedOrderEvent;
use Baraja\Shop\Warehouse\Entity\WarehouseCapacity;
use Baraja\Shop\Warehouse\WarehouseManager;
use Doctrine\ORM\EntityManagerInterface;

final class OrderWarehouseCreateOrderBridge implements CreatedOrderEvent
{
	public function __construct(
		private EntityManagerInterface $entityManager,
		private WarehouseManager $warehouseManager,
	) {
	}


	public function process(OrderInterface $order, string $expiration = '7 days'): void
	{
		foreach ($order->getItems() as $orderItem) {
			try {
				$product = $orderItem->getVariant() ?? $orderItem->getProduct();
			} catch (\Throwable) {
				// virtual product or error
				continue;
			}
			$this->warehouseManager->reserveCapacity(
				item: $product,
				quantity: $orderItem->getCount(),
				referenceHash: WarehouseCapacity::resolveOrderItemHash($order, $orderItem),
				expirationDate: new \DateTimeImmutable(sprintf('now + %s', $expiration)),
			);
		}
		$this->entityManager->flush();
	}
}
