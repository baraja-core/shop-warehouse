<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Entity;


use Baraja\Doctrine\Identifier\IdentifierUnsigned;
use Baraja\Localization\Translation;
use Baraja\Shop\Product\Entity\Product;
use Baraja\Shop\Product\Entity\ProductVariant;
use Doctrine\ORM\Mapping as ORM;

/**
 * A warehouse item that can be placed in a warehouse.
 * In e-shop terminology, it is a product. A warehouse is not only bound to products it contains its own warehouse
 * items to include things we don't necessarily want to sell, or special types of goods. Each product variant has its
 * own warehouse item because it is a different type of physical goods. Virtual products and some other product types
 * may not have a warehouse item (valid status).
 */
#[ORM\Entity]
#[ORM\Table(name: 'shop__warehouse_item')]
class WarehouseItem
{
	use IdentifierUnsigned;

	#[ORM\ManyToOne(targetEntity: Product::class)]
	private ?Product $product = null;

	#[ORM\ManyToOne(targetEntity: ProductVariant::class)]
	private ?ProductVariant $productVariant = null;

	#[ORM\Column(type: 'translate', nullable: true)]
	private ?Translation $name = null;

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
}
