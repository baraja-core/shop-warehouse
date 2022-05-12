<?php

declare(strict_types=1);

namespace Baraja\Shop\Warehouse;


use Baraja\Doctrine\ORM\DI\OrmAnnotationsExtension;
use Baraja\Plugin\Component\VueComponent;
use Baraja\Plugin\PluginComponentExtension;
use Baraja\Plugin\PluginManager;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;

final class WarehouseExtension extends CompilerExtension
{
	/**
	 * @return string[]
	 */
	public static function mustBeDefinedBefore(): array
	{
		return [OrmAnnotationsExtension::class];
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		PluginComponentExtension::defineBasicServices($builder);
		OrmAnnotationsExtension::addAnnotationPathToManager(
			$builder,
			'Baraja\Shop\Warehouse\Entity',
			__DIR__ . '/Entity',
		);

		$builder->addDefinition($this->prefix('warehouseManager'))
			->setFactory(WarehouseManager::class);

		/** @var ServiceDefinition $pluginManager */
		$pluginManager = $this->getContainerBuilder()->getDefinitionByType(PluginManager::class);
		$pluginManager->addSetup(
			'?->addComponent(?)', ['@self', [
				'key' => 'warehouseDefault',
				'name' => 'cms-warehouse-default',
				'implements' => WarehousePlugin::class,
				'componentClass' => VueComponent::class,
				'view' => 'default',
				'source' => __DIR__ . '/../template/default.js',
				'position' => 100,
				'tab' => 'Warehouse',
				'params' => [],
			]]
		);
		$pluginManager->addSetup(
			'?->addComponent(?)', ['@self', [
				'key' => 'warehouseOverview',
				'name' => 'cms-warehouse-overview',
				'implements' => WarehousePlugin::class,
				'componentClass' => VueComponent::class,
				'view' => 'detail',
				'source' => __DIR__ . '/../template/overview.js',
				'position' => 100,
				'tab' => 'Overview',
				'params' => ['id'],
			]]
		);
	}
}
