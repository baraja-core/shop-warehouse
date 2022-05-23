<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Entity;


use Baraja\Shop\Warehouse\Repository\WarehouseMoveProtocolRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarehouseMoveProtocolRepository::class)]
#[ORM\Table(name: 'shop__warehouse_move_protocol')]
class WarehouseMoveProtocol
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: WarehouseCapacity::class)]
	private WarehouseCapacity $capacity;

	/**
	 * This value reflects the actual number of items in stock at the time the move protocols are entered.
	 * If the change was for example only one item, it is always necessary
	 * to calculate the new stock value and write it to the log.
	 * Why are the actual stock values recorded and not just the differences?
	 * If we know the absolute values at each point in time, we can very efficiently calculate statistics,
	 * plan logistics and find out more quickly how many items are in which warehouse at the moment.
	 */
	#[ORM\Column(type: 'integer')]
	private int $quantity;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $updatedDate;


	public function __construct(WarehouseCapacity $capacity, int $quantity = 1)
	{
		if ($quantity === 0) {
			throw new \OutOfRangeException('Move quantity can not be zero.');
		}
		$this->capacity = $capacity;
		$this->quantity = $quantity;
		$this->updatedDate = new \DateTime;
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getCapacity(): WarehouseCapacity
	{
		return $this->capacity;
	}


	public function getQuantity(): int
	{
		return $this->quantity;
	}


	public function getUpdatedDate(): \DateTimeInterface
	{
		return $this->updatedDate;
	}
}
