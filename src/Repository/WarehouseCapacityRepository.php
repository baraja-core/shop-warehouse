<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Repository;


use Baraja\EcommerceStandard\DTO\WarehouseInterface;
use Baraja\EcommerceStandard\DTO\WarehouseItemInterface;
use Baraja\Shop\Warehouse\Entity\WarehouseCapacity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class WarehouseCapacityRepository extends EntityRepository
{
	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function findItem(
		WarehouseItemInterface $warehouseItem,
		?WarehouseInterface $warehouse = null,
	): WarehouseCapacity {
		$qb = $this->getBasicQuery($warehouseItem);
		if ($warehouse !== null) {
			$qb->andWhere('wc.warehouse = :warehouseId')
				->setParameter('warehouseId', $warehouse->getId());
		}

		return $qb
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
	}


	/**
	 * @return array<int, WarehouseCapacity>
	 */
	public function findItems(WarehouseItemInterface $warehouseItem): array
	{
		/** @var array<int, WarehouseCapacity> $return */
		$return = $this->getBasicQuery($warehouseItem)
			->getQuery()
			->getResult();

		return $return;
	}


	private function getBasicQuery(WarehouseItemInterface $warehouseItem): QueryBuilder
	{
		return $this->createQueryBuilder('wc')
			->join('wc.warehouse', 'warehouse')
			->andWhere('wc.item = :itemId')
			->andWhere('wc.quantity > 0')
			->setParameter('itemId', $warehouseItem->getId())
			->addOrderBy('warehouse.main', 'DESC')
			->addOrderBy('wc.quantity', 'DESC');
	}
}
