<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Entity;


use Baraja\EcommerceStandard\DTO\OrderInterface;
use Baraja\EcommerceStandard\DTO\OrderItemInterface;
use Baraja\EcommerceStandard\DTO\WarehouseCapacityInterface;
use Baraja\Shop\Warehouse\Repository\WarehouseCapacityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\UniqueConstraint(name: 'shop__warehouse_capacity_warehouse_item', columns: ['warehouse_id', 'item_id'])]
#[ORM\Entity(repositoryClass: WarehouseCapacityRepository::class)]
#[ORM\Table(name: 'shop__warehouse_capacity')]
class WarehouseCapacity implements WarehouseCapacityInterface
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: Warehouse::class)]
	private Warehouse $warehouse;

	#[ORM\ManyToOne(targetEntity: WarehouseItem::class)]
	private WarehouseItem $item;

	#[ORM\Column(type: 'integer')]
	private int $quantity = 0;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $location = null;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $updatedDate;


	public function __construct(Warehouse $warehouse, WarehouseItem $item, int $quantity = 1)
	{
		$this->warehouse = $warehouse;
		$this->item = $item;
		$this->setQuantity($quantity);
	}


	public static function resolveOrderItemHash(OrderInterface $order, OrderItemInterface $item): string
	{
		return sprintf('oi_%d_%s', $item->getId(), substr($order->getHash(), 0, 12)); // oi = OrderItem
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getWarehouse(): Warehouse
	{
		return $this->warehouse;
	}


	public function getItem(): WarehouseItem
	{
		return $this->item;
	}


	public function getQuantity(): int
	{
		return $this->quantity;
	}


	public function setQuantity(int $quantity): void
	{
		if ($quantity !== $this->quantity) {
			$this->updatedDate = new \DateTime;
		}
		$this->quantity = $quantity;
	}


	public function addQuantity(int $quantity): void
	{
		$this->setQuantity($this->quantity + $quantity);
	}


	public function getLocation(): ?string
	{
		return $this->location;
	}


	public function setLocation(?string $location): void
	{
		$this->location = $location;
	}


	public function getUpdatedDate(): \DateTimeInterface
	{
		return $this->updatedDate;
	}
}
