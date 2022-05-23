<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Bridge;


use Baraja\EcommerceStandard\DTO\OrderInterface;
use Baraja\EcommerceStandard\DTO\OrderItemInterface;
use Baraja\Shop\Warehouse\WarehouseManager;
use Doctrine\ORM\EntityManagerInterface;

final class OrderWarehouseBridge
{
	public function __construct(
		private EntityManagerInterface $entityManager,
		private WarehouseManager $warehouseManager,
	) {
	}


	public function onCreateOrder(OrderInterface $order, string $expiration = '7 days'): void
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
				referenceHash: $this->resolveCapacityHash($order, $orderItem),
				expirationDate: new \DateTimeImmutable(sprintf('now + %s', $expiration)),
			);
		}
		$this->entityManager->flush();
	}


	public function onPayOrder(OrderInterface $order): void
	{
		foreach ($order->getItems() as $orderItem) {
			$this->warehouseManager->clearCapacityReservationByHash(
				$this->resolveCapacityHash($order, $orderItem),
			);
		}
		$this->entityManager->flush();
	}


	private function resolveCapacityHash(OrderInterface $order, OrderItemInterface $item): string
	{
		return sprintf('oi_%d_%s', $item->getId(), substr($order->getHash(), 0, 12)); // oi = OrderItem
	}
}
