<?php
namespace ddGetDocumentField\Outputter;


abstract class Outputter extends \DDTools\BaseClass {
	protected
		/**
		 * @property $resourceFields {array} — Document fields including TVs used in the output.
		 * @property $resourceFields[i] {string} — Field name.
		 * @property $resourceFieldsAliases {stdClass} — Document field aliases for output.
		 * @property $resourceFieldsAliases->{$fieldName} {string} — An alias.
		 */
		$resourceFields = [],
		$resourceFieldsAliases = [],
		
		$typography = false,
		$escapeForJS = false,
		$URLEncode = false,
		$emptyResult = ''
	;
	
	/**
	 * __construct
	 * @version 1.0 (2020-04-25)
	 * 
	 * @param $params {stdClass|arrayAssociative}
	 * @param $params->dataProvider {\ddGetDocumentField\DataProvider\DataProvider}
	 */
	public function __construct($params = []){
		//Все параметры задают свойства объекта
		$this->setExistingProps($params);
		
		$this->resourceFieldsAliases = (object) $this->resourceFieldsAliases;
		
		$this->typography = boolval($this->typography);
		$this->escapeForJS = boolval($this->escapeForJS);
		$this->URLEncode = boolval($this->URLEncode);
		
		//Ask dataProvider to get them
		$params->dataProvider->addResourceFields($this->resourceFields);
	}
	
	/**
	 * render
	 * @version 1.0 (2020-04-25)
	 * 
	 * @param $resourceData {stdClass|arrayAssociative} — Resources fields. @required
	 * @param $resourceData->{$key} {string} — A field. @required
	 * 
	 * @return {string}
	 */
	public final function render($resourceData){
		$result = $this->emptyResult;
		
		//if resource data is not impty
		if (count((array) $resourceData) > 0){
			$resourceData = (object) $resourceData;
			
			//Apply aliases
			$resourceData = $this->render_resourceDataApplyAliases($resourceData);
			
			//Run outputter main render
			$result = $this->render_main($resourceData);
			
			//Typography
			if ($this->typography){
				$result = \ddTools::$modx->runSnippet(
					'ddTypograph',
					[
						'text' => $result
					]
				);
			}
		}
		
		//Если надо экранировать спец. символы
		if ($this->escapeForJS){
			$result = \ddTools::escapeForJS($result);
		}
		
		//Если нужно URL-кодировать строку
		if ($this->URLEncode){
			$result = rawurlencode($result);
		}
		
		return $result;
	}
	
	/**
	 * render_resourceDataApplyAliases
	 * @version 1.0 (2020-04-25)
	 * 
	 * @param $resourceData {stdClass} — Document fields. @required
	 * @param $resourceData->{$key} {string} — A field. @required
	 * 
	 * @return {string}
	 */
	private function render_resourceDataApplyAliases($resourceData){
		//IF aliases exists
		if (!empty($this->resourceFieldsAliases)){
			//Clear
			$result = (object) [];
			
			foreach (
				$this->resourceFieldsAliases as
				$fieldName =>
				$fieldAlias
			){
				//Use field name if alias is not set
				if (trim($fieldAlias) == ''){
					$fieldAlias = $fieldName;
				}
				
				//Save
				$result->{$fieldAlias} = $resourceData->{$fieldName};
			}
		}
		
		return $result;
	}
	
	/**
	 * render_main
	 * @version 1.0 (2020-04-25)
	 * 
	 * @param $resourceData {stdClass} — Document fields. @required
	 * @param $resourceData->{$key} {string} — A field. @required
	 * 
	 * @return {string}
	 */
	protected abstract function render_main($resourceData);
}