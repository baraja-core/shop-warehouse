<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Entity;


use Baraja\Localization\TranslateObject;
use Baraja\Localization\Translation;
use Baraja\Shop\Product\Entity\Product;
use Baraja\Shop\Product\Entity\ProductVariant;
use Baraja\Shop\Warehouse\Repository\WarehouseItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A warehouse item that can be placed in a warehouse.
 * In e-shop terminology, it is a product. A warehouse is not only bound to products it contains its own warehouse
 * items to include things we don't necessarily want to sell, or special types of goods. Each product variant has its
 * own warehouse item because it is a different type of physical goods. Virtual products and some other product types
 * may not have a warehouse item (valid status).
 *
 * @method Translation getName(?string $locale = null)
 * @method void setName(string $name, ?string $locale = null)
 */
#[ORM\Entity(repositoryClass: WarehouseItemRepository::class)]
#[ORM\Table(name: 'shop__warehouse_item')]
class WarehouseItem
{
	use TranslateObject;

	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: Product::class)]
	private ?Product $product;

	#[ORM\ManyToOne(targetEntity: ProductVariant::class)]
	private ?ProductVariant $productVariant;

	#[ORM\Column(type: 'translate', nullable: true)]
	private Translation $name;

	#[ORM\Column(type: 'string', unique: true, nullable: true)]
	private ?string $sku = null;

	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $ean = null;

	#[ORM\Column(type: 'float', nullable: true)]
	private ?float $totalPurchasePrice = null;

	#[ORM\Column(type: 'integer')]
	private int $minSupply = 0;

	#[ORM\Column(type: 'boolean')]
	private bool $quantityCanBeNegative = false;

	#[ORM\Column(type: 'boolean')]
	private bool $active = true;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $availableToDate = null;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $insertedDate;


	public function __construct(
		string $name,
		?Product $product = null,
		?ProductVariant $productVariant = null,
	) {
		$this->setName($name);
		$this->product = $product;
		$this->productVariant = $productVariant;
		$this->insertedDate = new \DateTimeImmutable('now');
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getProduct(): ?Product
	{
		return $this->product;
	}


	public function setProduct(?Product $product): void
	{
		$this->product = $product;
	}


	public function getProductVariant(): ?ProductVariant
	{
		return $this->productVariant;
	}


	public function setProductVariant(?ProductVariant $productVariant): void
	{
		$this->productVariant = $productVariant;
	}


	public function getSku(): ?string
	{
		return $this->sku;
	}


	public function setSku(?string $sku): void
	{
		$this->sku = $sku;
	}


	public function getEan(): ?string
	{
		return $this->ean;
	}


	public function setEan(?string $ean): void
	{
		$this->ean = $ean;
	}


	public function getTotalPurchasePrice(): ?float
	{
		return $this->totalPurchasePrice;
	}


	public function setTotalPurchasePrice(?float $totalPurchasePrice): void
	{
		$this->totalPurchasePrice = $totalPurchasePrice;
	}


	public function getMinSupply(): int
	{
		return $this->minSupply;
	}


	public function setMinSupply(int $minSupply): void
	{
		$this->minSupply = $minSupply;
	}


	public function isQuantityCanBeNegative(): bool
	{
		return $this->quantityCanBeNegative;
	}


	public function setQuantityCanBeNegative(bool $quantityCanBeNegative): void
	{
		$this->quantityCanBeNegative = $quantityCanBeNegative;
	}


	public function isActive(): bool
	{
		return $this->active;
	}


	public function setActive(bool $active): void
	{
		$this->active = $active;
	}


	public function getAvailableToDate(): ?\DateTimeInterface
	{
		return $this->availableToDate;
	}


	public function setAvailableToDate(?\DateTimeInterface $availableToDate): void
	{
		$this->availableToDate = $availableToDate;
	}


	public function getInsertedDate(): \DateTimeInterface
	{
		return $this->insertedDate;
	}
}
