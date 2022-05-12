<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse;


use Baraja\Doctrine\EntityManager;
use Baraja\Geocoder\GeocoderAccessor;
use Baraja\Shop\Warehouse\Entity\Warehouse;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class WarehouseManager
{
	public function __construct(
		private EntityManager $entityManager,
		private GeocoderAccessor $geocoderAccessor,
	) {
	}


	/**
	 * @return array<int, Warehouse>
	 */
	public function getWarehouses(): array
	{
		return $this->entityManager->getRepository(Warehouse::class)
			->createQueryBuilder('wh')
			->getQuery()
			->getResult();
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getById(int $id): Warehouse
	{
		return $this->entityManager->getRepository(Warehouse::class)
			->createQueryBuilder('wh')
			->where('wh.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getSingleResult();
	}


	public function createWarehouse(string $name): Warehouse
	{
		$warehouse = new Warehouse($name);
		$this->entityManager->persist($warehouse);
		$this->entityManager->flush();

		return $warehouse;
	}


	public function setLocation(Warehouse $warehouse, ?string $location): void
	{
		if ($location !== null && $warehouse->getLocation() !== $location) {
			$coordinates = $this->geocoderAccessor->get()->decode($location);
			$warehouse->setLatitude($coordinates->getLatitude());
			$warehouse->setLongitude($coordinates->getLongitude());
		}
		$warehouse->setLocation($location);
	}
}
