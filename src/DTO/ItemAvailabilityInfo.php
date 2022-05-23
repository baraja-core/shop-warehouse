<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\DTO;


use Baraja\Shop\Warehouse\Entity\Warehouse;
use Baraja\Shop\Warehouse\Entity\WarehouseCapacity;
use Baraja\Shop\Warehouse\Entity\WarehouseItem;

final class ItemAvailabilityInfo
{
	public function __construct(
		private Warehouse $warehouse,
		private WarehouseItem $warehouseItem,
		private WarehouseCapacity $warehouseCapacity,
	) {
	}
}
