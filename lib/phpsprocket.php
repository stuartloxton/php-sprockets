<?php
header('Content-type: text/plain');
/**
* PHPSprocket - A PHP implementation of Sprocket
*/
class PHPSprocket
{
	
	var $baseUri = '/phpsprocket';
	var $baseJs = '../';
	var $js = '';
	var $filePath = '';
	
	function __construct($file) {
		$this->filePath = str_replace($this->baseUri, '..', $file);
		$this->checkCached();
		$js = $this->parseJS(basename($this->filePath), dirname($this->filePath));
		echo $js;
	}
	
	function checkCached() {
		if(is_file($this->filePath.'.cache')) {
			echo file_get_contents($this->filePath.'.cache');
			exit;
		} else return false;
	}
	
	function parseJS($file, $context) {
		$js = file_get_contents($context.'/'.$file);
		
		preg_match_all('/\/\/= ([a-z]+) ([^\n]+)/', $js, $matches);
		
		foreach($matches[0] as $key => $match) {
			$method = $matches[1][$key].'_command';
			$js = str_replace($matches[0][$key], $this->$method(trim($matches[2][$key]), $context), $js);
		}
		
		return $js;
	}
	
	function require_command($param, $context) {
		if(preg_match('/\"([^\"]+)\"/', $param, $match)) {
			return $this->parseJS(basename($context.'/'.$match[1].'.js'), dirname($context.'/'.$match[1].'.js'));
		} else if(preg_match('/\<([^\>]+)\>/', $param, $match)) {
			return $this->parseJS(basename($context.'/'.$match[1].'.js'), $this->baseJS);
		} else return '';
	}
	
}

$sprocket = new PHPSprocket($_SERVER['REQUEST_URI']);
