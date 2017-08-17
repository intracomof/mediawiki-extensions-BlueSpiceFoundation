<?php
/**
 * Hook handler base class for BlueSpice hook BSEntityDelete
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceFoundation
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
namespace BlueSpice\Hook;
use BlueSpice\Hook;
use BlueSpice\Entity;

abstract class BSEntityDelete extends Hook {
	/**
	 * The Entity which will be deleted
	 * @var Entity
	 */
	protected $entity = null;

	/**
	 *
	 * @var \Status
	 */
	protected $status = null;

	/**
	 * User who performed this action
	 * @var \User
	 */
	protected $user = null;

	/**
	 * Located in \BlueSpice\Entity::delete. Before the entity will be deleted.
	 * Return a fatal $status to abort the deletion.
	 * @param Entity $entity
	 * @param \Status $status
	 * @param \User $user
	 * @return boolean
	 */
	public static function callback( $entity, $status, $user ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$entity,
			$status,
			$user
		);
		return $hookHandler->process();
	}

	/**
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param Entity $entity
	 * @param \Status $status
	 * @param \User $user
	 */
	public function __construct( $context, $config, $entity, $status, $user ) {
		parent::__construct( $context, $config );

		$this->entity = $entity;
		$this->status = $status;
		$this->user = $user;
	}
}