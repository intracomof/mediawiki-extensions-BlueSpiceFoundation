<?php
namespace BlueSpice\Utility;

use BlueSpice\TargetCache\Title\Target;

class PagePropHelper {

	/**
	 *
	 * @var \BlueSpice\Services
	 */
	protected $services;

	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 * @param Title $title
	 */
	public function __construct( $services, \Title $title ) {
		$this->title = $title;
		$this->services = $services;
	}

	/**
	 *
	 * @param string $name
	 * @param mixed|null $default
	 * @return string|$default
	 */
	public function getPageProp( $name, $default = null ) {
		$props = $this->getPageProps();
		if( !isset( $props[$name] ) ) {
			return $default;
		}

		return $props[$name];
	}

	/**
	 *
	 * @return \BlueSpice\TargetCacheHandler
	 */
	protected function getCache() {
		return $this->services->getBSTargetCacheTitle()->getHandler(
			'pageprops',
			new Target( $this->title )
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getPageProps() {
		if( !$this->title->exists() ) {
			return [];
		}
		if( $this->getCache()->get() ) {
			return $this->getCache()->get();
		}
		$pageProps = $this->loadPageProps();
		$this->getCache()->set( $pageProps );
		return $pageProps;
	}

	/**
	 * Fetches the page_props table
	 * @return array
	 */
	private function loadPageProps() {
		$pageProps = [];

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'page_props',
			[ 'pp_propname', 'pp_value' ],
			[ 'pp_page' => $this->title->getArticleID() ],
			__METHOD__
		);

		foreach( $res as $row ) {
			$pageProps[$row->pp_propname] = $row->pp_value;
		}
		return $pageProps;
	}

}