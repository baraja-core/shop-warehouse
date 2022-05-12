<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse\Entity;


use Baraja\Doctrine\Identifier\IdentifierUnsigned;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'shop__warehouse_move_protocol')]
final class WarehouseMoveProtocol
{
	use IdentifierUnsigned;
}
