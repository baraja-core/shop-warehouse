<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse;


use Baraja\Doctrine\EntityManager;
use Baraja\EcommerceStandard\DTO\ProductInterface;
use Baraja\EcommerceStandard\DTO\ProductVariantInterface;
use Baraja\EcommerceStandard\DTO\WarehouseCapacityInterface;
use Baraja\EcommerceStandard\DTO\WarehouseInterface;
use Baraja\EcommerceStandard\DTO\WarehouseItemInterface;
use Baraja\EcommerceStandard\DTO\WarehouseItemReservationInterface;
use Baraja\EcommerceStandard\Service\WarehouseManagerInterface;
use Baraja\Geocoder\GeocoderAccessor;
use Baraja\Lock\Lock;
use Baraja\Shop\Product\Entity\Product;
use Baraja\Shop\Product\Entity\ProductVariant;
use Baraja\Shop\Warehouse\DTO\ItemAvailabilityInfo;
use Baraja\Shop\Warehouse\Entity\Warehouse;
use Baraja\Shop\Warehouse\Entity\WarehouseCapacity;
use Baraja\Shop\Warehouse\Entity\WarehouseItem;
use Baraja\Shop\Warehouse\Entity\WarehouseItemReservation;
use Baraja\Shop\Warehouse\Entity\WarehouseMoveProtocol;
use Baraja\Shop\Warehouse\Repository\WarehouseCapacityRepository;
use Baraja\Shop\Warehouse\Repository\WarehouseItemRepository;
use Baraja\Shop\Warehouse\Repository\WarehouseItemReservationRepository;
use Baraja\Shop\Warehouse\Repository\WarehouseRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class WarehouseManager implements WarehouseManagerInterface
{
	private WarehouseRepository $warehouseRepository;

	private WarehouseCapacityRepository $capacityRepository;

	private WarehouseItemRepository $itemRepository;

	private WarehouseItemReservationRepository $itemReservationRepository;


	public function __construct(
		private EntityManager $entityManager,
		private GeocoderAccessor $geocoderAccessor,
	) {
		$warehouseRepository = $entityManager->getRepository(Warehouse::class);
		$capacityRepository = $entityManager->getRepository(WarehouseCapacity::class);
		$itemRepository = $entityManager->getRepository(WarehouseItem::class);
		$itemReservationRepository = $entityManager->getRepository(WarehouseItemReservation::class);
		assert($warehouseRepository instanceof WarehouseRepository);
		assert($capacityRepository instanceof WarehouseCapacityRepository);
		assert($itemRepository instanceof WarehouseItemRepository);
		assert($itemReservationRepository instanceof WarehouseItemReservationRepository);
		$this->warehouseRepository = $warehouseRepository;
		$this->capacityRepository = $capacityRepository;
		$this->itemRepository = $itemRepository;
		$this->itemReservationRepository = $itemReservationRepository;
	}


	/**
	 * @return array<int, Warehouse>
	 */
	public function getWarehouses(): array
	{
		return $this->warehouseRepository->getList();
	}


	public function getMainWarehouse(): Warehouse
	{
		try {
			$warehouse = $this->warehouseRepository->getMain();
		} catch (NoResultException|NonUniqueResultException) {
			$warehouse = $this->createWarehouse('Main');
		}
		if ($warehouse->isMain() === false) {
			$warehouse->setMain(true);
			$this->entityManager->flush();
		}

		return $warehouse;
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getById(int $id): Warehouse
	{
		return $this->warehouseRepository->getById($id);
	}


	public function createWarehouse(string $name): Warehouse
	{
		$warehouse = new Warehouse($name);
		$this->entityManager->persist($warehouse);
		$this->entityManager->flush();

		return $warehouse;
	}


	public function getWarehouseItem(ProductInterface|ProductVariantInterface|WarehouseItemInterface|string $item
	): WarehouseItem {
		if ($item instanceof WarehouseItem) {
			return $item;
		}
		try {
			return $this->itemRepository->findItem($item);
		} catch (NoResultException|NonUniqueResultException) {
			return $this->createWarehouseItem($item);
		}
	}


	public function createWarehouseItem(ProductInterface|ProductVariantInterface|string $item): WarehouseItem
	{
		$product = null;
		$productVariant = null;
		if ($item instanceof ProductVariantInterface) {
			$productVariant = $item;
			$product = $item->getProduct();
			$name = $item->getLabel();
		} elseif ($item instanceof ProductInterface) {
			$product = $item;
			$name = $item->getLabel();
		} else {
			$name = $item;
		}

		$return = new WarehouseItem(
			name: $name,
			product: $product instanceof Product ? $product : null,
			productVariant: $productVariant instanceof ProductVariant ? $productVariant : null,
		);
		$this->entityManager->persist($return);
		$this->entityManager->flush();

		return $return;
	}


	public function setLocation(WarehouseInterface $warehouse, ?string $location): void
	{
		if ($location !== null && $warehouse->getLocation() !== $location) {
			$coordinates = $this->geocoderAccessor->get()->decode($location);
			$warehouse->setLatitude($coordinates->getLatitude());
			$warehouse->setLongitude($coordinates->getLongitude());
		}
		$warehouse->setLocation($location);
	}


	public function getRealCapacity(ProductInterface|ProductVariantInterface|WarehouseItemInterface|string $item): int
	{
		$total = $this->getTotalCapacity($item);
		$reserved = $this->getReservedCapacity($item);

		if ($reserved > $total) {
			throw new \LogicException(
				sprintf(
					'Reserved quantity (%d) is more than total real capacity (%d).',
					$reserved,
					$total,
				),
			);
		}

		return $total - $reserved;
	}


	public function getTotalCapacity(ProductInterface|ProductVariantInterface|WarehouseItemInterface|string $item): int
	{
		$warehouseItem = $this->getWarehouseItem($item);

		$return = 0;
		foreach ($this->capacityRepository->findItems($warehouseItem) as $capacityItem) {
			$return += $capacityItem->getQuantity();
		}

		return $return;
	}


	public function getReservedCapacity(ProductInterface|ProductVariantInterface|WarehouseItemInterface|string $item
	): int {
		$warehouseItem = $this->getWarehouseItem($item);

		$return = 0;
		foreach ($this->itemReservationRepository->findItems($warehouseItem) as $reservation) {
			$return += $reservation->getQuantity();
		}

		return $return;
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getCapacity(WarehouseItemInterface $item, ?WarehouseInterface $warehouse = null): WarehouseCapacity
	{
		return $this->capacityRepository->findItem($item, $warehouse);
	}


	public function reserveCapacity(
		ProductInterface|ProductVariantInterface|WarehouseItemInterface|string $item,
		int $quantity,
		?string $referenceHash = null,
		?\DateTimeInterface $expirationDate = null,
	): void {
		$warehouseItem = $this->getWarehouseItem($item);
		$lockKey = sprintf('warehouse-item-%d', $warehouseItem->getId());
		Lock::startTransaction($lockKey);

		$availableCapacities = $this->capacityRepository->findItems($warehouseItem);

		$sumCapacity = 0;
		$fullMatchingCapacity = null;
		foreach ($availableCapacities as $capacity) {
			$sumCapacity += $capacity->getQuantity();
			if ($capacity->getQuantity() >= $quantity) {
				$fullMatchingCapacity = $capacity;
				break;
			}
		}

		if ($quantity > $sumCapacity) {
			Lock::stopTransaction($lockKey);
			throw new \OutOfRangeException(
				sprintf(
					'Can not make reservation, because %d items are not available. Real capacity is %d items only.',
					$quantity,
					$sumCapacity,
				),
			);
		}

		$capacities = $fullMatchingCapacity !== null
			? [$fullMatchingCapacity]
			: $availableCapacities;

		foreach ($capacities as $realCapacity) {
			if ($quantity <= 0) {
				break;
			}
			$reservationQuantity = min($realCapacity->getQuantity(), $quantity);
			$quantity -= $reservationQuantity;
			$this->entityManager->persist(
				new WarehouseItemReservation(
					capacity: $realCapacity,
					quantity: $reservationQuantity,
					expirationDate: $expirationDate ?? new \DateTimeImmutable('now + 30 days'),
					referenceHash: $referenceHash,
				),
			);
		}

		$this->entityManager->flush();
		Lock::stopTransaction($lockKey);
	}


	public function clearCapacityReservationByHash(string $hash): void
	{
		foreach ($this->itemReservationRepository->getByHash($hash) as $reservation) {
			$this->entityManager->remove($reservation);
		}
	}


	public function changeCapacity(
		ProductInterface|ProductVariantInterface|WarehouseItemInterface|WarehouseCapacityInterface|string $item,
		int $quantity,
		?WarehouseInterface $warehouse = null,
	): void {
		if ($item instanceof WarehouseCapacity) {
			$capacity = $item;
		} else {
			$warehouse ??= $this->getMainWarehouse();
			$warehouseItem = $this->getWarehouseItem($item);
			try {
				$capacity = $this->getCapacity($warehouseItem, $warehouse);
			} catch (NoResultException|NonUniqueResultException) {
				$capacity = new WarehouseCapacity($warehouse, $warehouseItem, $quantity);
				$this->entityManager->persist($capacity);
			}
		}

		$currentQuantity = $capacity->getQuantity();
		if ($currentQuantity !== $quantity) {
			$capacity->setQuantity($quantity);
			$this->entityManager->persist(new WarehouseMoveProtocol($capacity, $quantity));
		}

		$this->entityManager->flush();
	}


	public function transformReservationToChangeCapacity(WarehouseItemReservationInterface|string $reservation): void
	{
		$reservations = is_string($reservation)
			? $this->itemReservationRepository->getByHash($reservation)
			: [$reservation];

		foreach ($reservations as $reservationItem) {
			$this->entityManager->remove($reservationItem);
			$capacity = $reservation->getCapacity();
			if ($capacity->getQuantity() < $reservationItem->getQuantity()) {
				throw new \OutOfRangeException(
					sprintf(
						'Warehouse item is not available in selected capacity (id: %d). Capacity %d requested, but capacity %d is really available.',
						$capacity->getId(),
						$reservationItem->getQuantity(),
						$capacity->getQuantity(),
					),
				);
			}
			$this->changeCapacity(
				item: $capacity,
				quantity: $capacity->getQuantity() - $reservationItem->getQuantity(),
				warehouse: $capacity->getWarehouse(),
			);
		}
		$this->entityManager->flush();
	}


	/**
	 * @return array<int, ItemAvailabilityInfo>
	 */
	public function getItemAvailability(ProductInterface|ProductVariantInterface $item): array
	{
		$warehouseItem = $this->getWarehouseItem($item);

		$return = [];
		foreach ($this->capacityRepository->findItems($warehouseItem) as $capacity) {
			$return[] = new ItemAvailabilityInfo($capacity->getWarehouse(), $warehouseItem, $capacity);
		}

		return $return;
	}
}
