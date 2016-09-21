<?php

/**
 * BSEntityConfig class for BlueSpice
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceFoundation
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * BSEntityConfig class for BlueSpice
 * @package BlueSpiceFoundation
 */
abstract class BSEntityConfig {
	protected static $aEntityConfigs = null;
	protected static $aDefaults = array();
	protected $sType = '';

	/**
	 * BSEntityConfig factory
	 * @param string $sType - Entity type
	 * @return BSEntityConfig - or null
	 */
	public static function factory( $sType ) {
		if( !is_null(static::$aEntityConfigs) ) {
			if( !isset(static::$aEntityConfigs[$sType]) ) {
				return null;
			}
			return static::$aEntityConfigs[$sType];
		}
		//TODO: Check params and classes
		$aRegisteredEntities = BSEntityRegistry::getRegisteredEntities();
		foreach( $aRegisteredEntities as $sKey => $sConfigClass ) {
			static::$aEntityConfigs[$sKey] = new $sConfigClass();
			static::$aEntityConfigs[$sKey]->sType = $sKey;
			array_merge(
				static::$aDefaults,
				static::$aEntityConfigs[$sKey]->addGetterDefaults()
			);
		}
		Hooks::run( 'BSEntityConfigDefaults', array( &static::$aDefaults ) );
		return static::$aEntityConfigs[$sType];
	}

	protected static function getDefault( $sMethod ) {
		if( !isset(static::$aDefaults[$sMethod]) ) {
			return false;
		}
		return static::$aDefaults[$sMethod];
	}

	/**
	 * Getter for config methods
	 * @param string $sMethod
	 * @return mixed - The return value of the internaly called method or the
	 * default
	 */
	public function get( $sMethod ) {
		$sMethod = "get_$sMethod";
		if( !is_callable( array($this, $sMethod) ) ) {
			static::getDefault( $sMethod );
		}
		return $this->$sMethod();
	}

	abstract protected function addGetterDefaults();
	abstract protected function get_EntityClass();

	protected function get_ContentClass() {
		return 'BSEntityContent';
	}
}