<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Entity;


use Baraja\Doctrine\Identifier\IdentifierUnsigned;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'shop__warehouse_capacity')]
class WarehouseCapacity
{
	use IdentifierUnsigned;

	#[ORM\ManyToOne(targetEntity: Warehouse::class)]
	private Warehouse $warehouse;

	#[ORM\ManyToOne(targetEntity: WarehouseItem::class)]
	private WarehouseItem $item;

	#[ORM\Column(type: 'integer')]
	private int $quantity;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $updatedDate;


	public function __construct(Warehouse $warehouse, WarehouseItem $item, int $quantity = 1)
	{
		$this->warehouse = $warehouse;
		$this->item = $item;
		$this->setQuantity($quantity);
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


	public function getUpdatedDate(): \DateTimeInterface
	{
		return $this->updatedDate;
	}
}
