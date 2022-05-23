<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Repository;


use Baraja\Shop\Warehouse\Entity\Warehouse;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class WarehouseRepository extends EntityRepository
{
	private ?Warehouse $mainWarehouse = null;


	/**
	 * @return array<int, Warehouse>
	 */
	public function getList(): array
	{
		return $this->createQueryBuilder('wh')
			->getQuery()
			->getResult();
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getById(int $id): Warehouse
	{
		return $this->createQueryBuilder('wh')
			->where('wh.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getSingleResult();
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getMain(): Warehouse
	{
		if ($this->mainWarehouse !== null) {
			return $this->mainWarehouse;
		}

		$warehouse = $this->createQueryBuilder('wh')
			->orderBy('wh.main', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($warehouse instanceof Warehouse);
		if ($warehouse->isMain()) {
			$this->mainWarehouse = $warehouse;
		}

		return $warehouse;
	}
}
