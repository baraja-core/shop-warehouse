<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Entity;


use Baraja\EcommerceStandard\DTO\WarehouseInterface;
use Baraja\Localization\TranslateObject;
use Baraja\Localization\Translation;
use Baraja\Shop\Warehouse\Repository\WarehouseRepository;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * @method Translation getName(?string $locale = null)
 * @method void setName(string $name, ?string $locale = null)
 * @method Translation|null getDescription(?string $locale = null)
 * @method void setDescription(?string $description, ?string $locale = null)
 */
#[ORM\Index(columns: ['main'])]
#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
#[ORM\Table(name: 'shop__warehouse')]
class Warehouse implements WarehouseInterface
{
	use TranslateObject;

	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\Column(type: 'translate')]
	protected Translation $name;

	#[ORM\Column(type: 'translate', nullable: true)]
	protected ?Translation $description = null;

	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $location = null;

	#[ORM\Column(type: 'float', nullable: true)]
	private ?float $latitude = null;

	#[ORM\Column(type: 'float', nullable: true)]
	private ?float $longitude = null;

	#[ORM\Column(type: 'boolean')]
	private bool $main = false;

	#[ORM\Column(type: 'boolean')]
	private bool $quantityCanBeNegative = false;

	#[ORM\Column(type: 'integer')]
	private int $defaultMinimalQuantity = 0;


	public function __construct(string $name)
	{
		$name = trim($name);
		if ($name === '') {
			throw new \InvalidArgumentException('Warehouse name can not be empty.');
		}
		$this->setName($name);
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getLabel(): string
	{
		return (string) $this->getName();
	}


	public function getLocation(): ?string
	{
		return $this->location;
	}


	public function setLocation(?string $location): void
	{
		if ($location !== null) {
			$location = Strings::firstUpper(trim((string) preg_replace('/\s+/', ' ', $location)));
		}
		$this->location = $location;
	}


	public function getLatitude(): ?float
	{
		return $this->latitude;
	}


	public function setLatitude(?float $latitude): void
	{
		$this->latitude = $latitude;
	}


	public function getLongitude(): ?float
	{
		return $this->longitude;
	}


	public function setLongitude(?float $longitude): void
	{
		$this->longitude = $longitude;
	}


	public function isMain(): bool
	{
		return $this->main;
	}


	public function setMain(bool $main): void
	{
		$this->main = $main;
	}


	public function isQuantityCanBeNegative(): bool
	{
		return $this->quantityCanBeNegative;
	}


	public function setQuantityCanBeNegative(bool $quantityCanBeNegative): void
	{
		$this->quantityCanBeNegative = $quantityCanBeNegative;
	}


	public function getDefaultMinimalQuantity(): int
	{
		return $this->defaultMinimalQuantity;
	}


	public function setDefaultMinimalQuantity(int $defaultMinimalQuantity): void
	{
		$this->defaultMinimalQuantity = $defaultMinimalQuantity;
	}
}
