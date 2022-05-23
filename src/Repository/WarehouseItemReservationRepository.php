<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Repository;


use Baraja\Shop\Warehouse\Entity\WarehouseItem;
use Baraja\Shop\Warehouse\Entity\WarehouseItemReservation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class WarehouseItemReservationRepository extends EntityRepository
{
	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getByHash(string $hash): WarehouseItemReservation
	{
		$reservation = $this->createQueryBuilder('r')
			->where('r.referenceHash = :hash')
			->setParameter('hash', $hash)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($reservation instanceof WarehouseItemReservation);

		return $reservation;
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
