<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Repository;


use Baraja\EcommerceStandard\DTO\ProductInterface;
use Baraja\EcommerceStandard\DTO\ProductVariantInterface;
use Baraja\Shop\Warehouse\Entity\WarehouseItem;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

final class WarehouseItemRepository extends EntityRepository
{
	/** @var array<string, WarehouseItem> */
	private static array $cache = [];


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getById(int $id): WarehouseItem
	{
		return $this->createQueryBuilder('wi')
			->where('wi.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getSingleResult();
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function findItem(ProductInterface|ProductVariantInterface|string $item): WarehouseItem
	{
		$hash = is_object($item) ? sprintf('%s_%d', $item::class, $item->getId()) : $item;
		if (isset(self::$cache[$hash])) {
			return self::$cache[$hash];
		}

		$return = $this->getBasicQuery($item)
			->getQuery()
			->getSingleResult();
		assert($return instanceof WarehouseItem);
		self::$cache[$hash] = $return;

		return $return;
	}


	private function getBasicQuery(ProductInterface|ProductVariantInterface|string $item): QueryBuilder
	{
		$qb = $this->createQueryBuilder('item');

		if ($item instanceof ProductVariantInterface) {
			$qb
				->andWhere('item.product = :productId')
				->andWhere('item.productVariant = :productVariantId')
				->setParameter('productId', $item->getProduct()->getId())
				->setParameter('productVariantId', $item->getId());
		} elseif ($item instanceof ProductInterface) {
			$qb
				->andWhere('item.product = :productId')
				->setParameter('productId', $item->getId());
		} else {
			$qb
				->andWhere('item.sku = :code OR item.ean = :code')
				->setParameter('code', $item);
		}

		return $qb;
	}
}
