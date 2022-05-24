<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\DTO;


use Baraja\EcommerceStandard\DTO\WarehouseItemAvailabilityInfoInterface;
use Baraja\Shop\Warehouse\Entity\Warehouse;
use Baraja\Shop\Warehouse\Entity\WarehouseCapacity;
use Baraja\Shop\Warehouse\Entity\WarehouseItem;

final class ItemAvailabilityInfo implements WarehouseItemAvailabilityInfoInterface
{
	public function __construct(
		private Warehouse $warehouse,
		private WarehouseItem $warehouseItem,
		private WarehouseCapacity $warehouseCapacity,
	) {
	}


	public function getWarehouse(): Warehouse
	{
		return $this->warehouse;
	}


	public function getWarehouseItem(): WarehouseItem
	{
		return $this->warehouseItem;
	}


	public function getWarehouseCapacity(): WarehouseCapacity
	{
		return $this->warehouseCapacity;
	}
}
