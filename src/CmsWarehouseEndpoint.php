<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse;


use Baraja\Doctrine\EntityManager;
use Baraja\StructuredApi\BaseEndpoint;

final class CmsWarehouseEndpoint extends BaseEndpoint
{
	public function __construct(
		private WarehouseManager $warehouseManager,
		private EntityManager $entityManager,
	) {
	}


	public function actionDefault(): void
	{
		$warehouses = [];
		foreach ($this->warehouseManager->getWarehouses() as $warehouse) {
			$warehouses[] = [
				'id' => $warehouse->getId(),
				'name' => $warehouse->getName(),
				'description' => $warehouse->getDescription(),
				'location' => $warehouse->getLocation(),
			];
		}

		$this->sendJson(
			[
				'warehouses' => $warehouses,
			]
		);
	}


	public function actionDetail(int $id): void
	{
		$warehouse = $this->warehouseManager->getById($id);

		$this->sendJson(
			[
				'id' => $warehouse->getId(),
				'name' => $warehouse->getName(),
				'description' => $warehouse->getDescription(),
				'defaultMinimalQuantity' => $warehouse->getDefaultMinimalQuantity(),
				'quantityCanBeNegative' => $warehouse->isQuantityCanBeNegative(),
				'location' => $warehouse->getLocation(),
				'longitude' => $warehouse->getLongitude(),
				'latitude' => $warehouse->getLatitude(),
			]
		);
	}


	public function postSaveDetail(
		int $id,
		string $name,
		int $defaultMinimalQuantity,
		bool $quantityCanBeNegative,
		?string $description = null,
		?string $location = null,
	): void {
		$warehouse = $this->warehouseManager->getById($id);
		$warehouse->setName($name);
		$warehouse->setDescription($description);
		$warehouse->setDefaultMinimalQuantity($defaultMinimalQuantity);
		$warehouse->setQuantityCanBeNegative($quantityCanBeNegative);
		$this->warehouseManager->setLocation($warehouse, $location);

		$this->flashMessage('Warehouse has been updated.', self::FLASH_MESSAGE_SUCCESS);
		$this->entityManager->flush();
		$this->sendOk();
	}


	public function postCreateWarehouse(string $name): void
	{
		$this->warehouseManager->createWarehouse($name);
		$this->flashMessage('Warehouse has been created.', self::FLASH_MESSAGE_SUCCESS);
		$this->sendOk();
	}
}
