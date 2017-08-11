<?php

namespace BlueSpice\Tag;

use BlueSpice\Tag\MarkerType;

class GenericHandler {

	const TAG_DIV = 'div';
	const TAG_SPAN = 'span';
	const TAG_BUTTON = 'button';

	protected function getValidElementNames () {
		return [
			static::TAG_BUTTON,
			static::TAG_DIV,
			static::TAG_SPAN
		];
	}
	/**
	 *
	 * @var ITag
	 */
	protected $tag = null;

	/**
	 *
	 * @var error
	 */
	protected $errors = [];

	/**
	 *
	 * @var string
	 */
	protected $input = '';

	/**
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 *
	 * @var \Parser
	 */
	protected $parser = null;

	/**
	 *
	 * @var mixed
	 */
	protected $processedInput = '';

	/**
	 *
	 * @var array
	 */
	protected $processedArgs = [];

	/**
	 *
	 * @param ITag $tag
	 */
	public function __construct( $tag ) {
		$this->tag = $tag;
	}

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return array
	 * @throws \MWException
	 */
	public function handle( $input, array $args, \Parser $parser, \PPFrame $frame ) {
		$elementName = $this->tag->getContainerElementName();
		if( !$this->isValidContainerElementName( $elementName ) ) {
			$tagNames = $this->tag->getTagNames();
			throw new \MWException(
				"Invalid container element name for tag '{$tagNames[0]}'!"
			);
		}

		$this->parser = $parser;
		$this->input = $input;
		$this->args = $args;

		$this->processInput();
		$this->processArgs();

		if( $this->hasErrors() ) {
			return $this->makeErrorOutput();
		}

		if( $this->tag->needsDisabledParserCache() ) {
			$this->parser->getOutput()->updateCacheExpiry( 0 );
		}

		$this->addResourceLoaderModules();

		$handler = $this->tag->getHandler(
			$this->processedInput,
			$this->processedArgs,
			$parser,
			$frame
		);

		try {
			$output = $handler->handle();
		} catch( \Exception $ex) {
			//TODO: Find way to output "hidden" trace
			$this->errors[] = $ex->getMessage();
			return $this->makeErrorOutput();
		}

		$wrappedOutput = \Html::rawElement(
			$elementName,
			$this->makeContainerAttributes(),
			$output
		);

		return [
			$wrappedOutput,
			MarkerType::KEY => (string) $this->tag->getMarkerType()
		];
	}

	/**
	 * @return array
	 */
	protected function makeContainerAttributes() {
		$cssClasses = [ 'bs-tag' ];
		foreach( $this->tag->getTagNames() as $tagName ) {
			$cssClasses[] = \Sanitizer::escapeClass( "bs-tag-$tagName" );
		}

		$attribs = [
			'class' => implode( ' ', array_unique( $cssClasses ) )
		];

		foreach( $this->args as $argName => $argValue ) {
			$attribs["data-bs-arg-$argName"] = $argValue;
		}

		$attribs["data-bs-input"] = $this->input;

		return $attribs;
	}

	protected function processInput() {
		$this->processedInput = $this->input;
		if( $this->tag->needsParsedInput() ) {
			$this->processedInput =
				$this->parser->recursiveTagParse( $this->processedInput );
		}

		$paramDefinition = $this->tag->getInputDefinition();
		if( $paramDefinition === null ) {
			return;
		}
		$paramName = $paramDefinition->getName();
		$paramDefinition->setMessage(
			wfMessage( 'bs-tag-input-desc' )->plain()
		);

		$processor = \ParamProcessor\Processor::newDefault();
		$processor->setParameters(
			[ $paramName => $this->processedInput ],
			[ $paramDefinition ]
		);

		$result = $processor->processParameters();
		$this->checkForProcessingErrors( $result );
		$processedParams = $result->getParameters();

		$this->processedInput = $processedParams[ $paramName ]->getValue();
	}

	protected function processArgs() {
		$this->processedArgs = $this->args;
		if( $this->tag->needsParseArgs() ) {
			foreach( $this->processedArgs as &$processedArg ) {
				$processedArg =
					$this->parser->recursiveTagParse( $processedArg );
			}
		}

		$paramDefinitions = $this->tag->getArgsDefinitions();
		if( empty( $paramDefinitions ) ) {
			return;
		}
		foreach( $paramDefinitions as $paramDefinition ) {
			$paramDefinition->setMessage(
				wfMessage(
					'bs-tag-param-desc',
					$paramDefinition->getName()
				)->plain()
			);
		}

		$processor = \ParamProcessor\Processor::newDefault();
		$processor->setParameters( $this->processedArgs, $paramDefinitions );

		$result = $processor->processParameters();
		$this->checkForProcessingErrors( $result );

		$this->processedArgs = [];
		foreach( $result->getParameters() as $processedParam ) {
			$this->processedArgs[$processedParam->getName()]
				= $processedParam->getValue();
		}
	}

	protected function hasErrors() {
		return !empty( $this->errors );
	}

	protected function makeErrorOutput() {
		$out = [];
		foreach( $this->errors as $errorKey => $errorMessage ) {
			$out[] = \Html::element(
				'div',
				[ 'class' => 'bs-error bs-tag' ],
				$errorMessage
			);
		}
		return implode( "\n", $out );
	}

	protected function isValidContainerElementName( $elementName ) {
		return in_array( $elementName, $this->getValidElementNames() );
	}

	protected function addResourceLoaderModules() {
		$modules = $this->tag->getResourceLoaderModules();
		foreach( $modules as $moduleName ) {
			$this->parser->getOutput()->addModules( $moduleName );
		}

		$moduleStyles = $this->tag->getResourceLoaderModuleStyles();
		foreach( $moduleStyles as $moduleStyleName ) {
			$this->parser->getOutput()->addModuleStyles( $moduleStyleName );
		}
	}

	/**
	 *
	 * @param \ParamProcessor\ProcessingResult $result
	 */
	protected function checkForProcessingErrors( $result ) {
		foreach( $result->getErrors() as $error ) {
			$this->errors[] = $error->getMessage();
		}
	}
}