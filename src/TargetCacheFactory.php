<?php

namespace BlueSpice;

use BlueSpice\Utility\CacheHelper;

class TargetCacheFactory {

	/**
	 *
	 * @var IRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var CacheHelper
	 */
	protected $cacheHelper = null;

	/**
	 *
	 * @var ITargetCache[]
	 */
	protected $instances = [];

	/**
	 *
	 * @param IRegistry $registry
	 * @param \Config $config
	 */
	public function __construct( $registry, \Config $config, CacheHelper $cacheHelper ) {
		$this->registry = $registry;
		$this->config = $config;
		$this->cacheHelper = $cacheHelper;
	}

	/**
	 *
	 * @param string $key
	 * @return TargetCache
	 */
	public function get( $key ) {
		$className = $this->registry->getValue(
			$key,
			false
		);
		$instance = new $className( $key, $this->config, $this->cacheHelper );

		return $instance;
	}
}
