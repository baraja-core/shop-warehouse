<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Entity;


use Baraja\Shop\Warehouse\Repository\WarehouseItemReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarehouseItemReservationRepository::class)]
#[ORM\Table(name: 'shop__warehouse_item_reservation')]
class WarehouseItemReservation
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: WarehouseCapacity::class)]
	private WarehouseCapacity $capacity;

	#[ORM\Column(type: 'string', length: 32, unique: true, nullable: true)]
	private ?string $referenceHash;

	#[ORM\Column(type: 'integer')]
	private int $quantity;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $expirationDate;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $insertedDate;


	public function __construct(
		WarehouseCapacity $capacity,
		int $quantity,
		?\DateTimeInterface $expirationDate = null,
		?string $referenceHash = null,
	) {
		$this->capacity = $capacity;
		$this->quantity = $quantity;
		$this->expirationDate = $expirationDate;
		$this->referenceHash = $referenceHash;
		$this->insertedDate = new \DateTimeImmutable('now');
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getCapacity(): WarehouseCapacity
	{
		return $this->capacity;
	}


	public function getReferenceHash(): ?string
	{
		return $this->referenceHash;
	}


	public function getQuantity(): int
	{
		return $this->quantity;
	}


	public function getExpirationDate(): ?\DateTimeInterface
	{
		return $this->expirationDate;
	}


	public function setExpirationDate(?\DateTimeInterface $expirationDate): void
	{
		$this->expirationDate = $expirationDate;
	}


	public function getInsertedDate(): \DateTimeInterface
	{
		return $this->insertedDate;
	}
}
