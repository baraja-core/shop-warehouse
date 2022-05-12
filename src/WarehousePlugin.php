<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse;


use Baraja\Plugin\BasePlugin;

final class WarehousePlugin extends BasePlugin
{
	public function __construct(
		private WarehouseManager $warehouseManager,
	) {
	}


	public function getName(): string
	{
		return 'Warehouse';
	}


	public function actionDetail(int $id): void
	{
		$warehouse = $this->warehouseManager->getById($id);
		$this->setTitle('(' . $id . ') ' . $warehouse->getName());
	}
}
