<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Repository;


use Baraja\Shop\Warehouse\Entity\WarehouseItem;
use Baraja\Shop\Warehouse\Entity\WarehouseItemReservation;
use Doctrine\ORM\EntityRepository;

final class WarehouseItemReservationRepository extends EntityRepository
{
	/**
	 * @return array<int, WarehouseItemReservation>
	 */
	public function getByHash(string $hash): array
	{
		/** @var array<int, WarehouseItemReservation> $reservations */
		$reservations = $this->createQueryBuilder('r')
			->where('r.referenceHash = :hash')
			->setParameter('hash', $hash)
			->getQuery()
			->getResult();

		return $reservations;
	}


	/**
	 * @return array<int, WarehouseItemReservation>
	 */
	public function findItems(WarehouseItem $warehouseItem): array
	{
		/** @var array<int, WarehouseItemReservation> $return */
		$return = $this->createQueryBuilder('wr')
			->join('wr.capacity', 'capacity')
			->join('capacity.warehouse', 'warehouse')
			->andWhere('capacity.item = :itemId')
			->andWhere('wr.expirationDate IS NULL OR wr.expirationDate > :now')
			->setParameter('itemId', $warehouseItem->getId())
			->setParameter('now', new \DateTimeImmutable)
			->addOrderBy('warehouse.main', 'DESC')
			->getQuery()
			->getResult();

		return $return;
	}
}
