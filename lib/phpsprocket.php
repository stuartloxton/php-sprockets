<?php
header('Content-type: application/x-javascript');
/**
* PHPSprocket - A PHP implementation of Sprocket
*/
class PHPSprocket
{
	
	var $baseUri = '/phpsprocket';
	var $baseJs = '../js';
	var $js = '';
	var $filePath = '';
	var $assetFolder = '..';
	
	var $constantsScanned = array();
	var $constants = array();
	
	function __construct($file) {
		$this->filePath = str_replace($this->baseUri, '..', $file);
		if(!isset($_GET['debug'])) $this->checkCached();
		$this->js = $this->parseJS(basename($this->filePath), dirname($this->filePath));
		if(count($this->constants) > 0) $this->swapConstants();
		$this->stripComments();
		echo $this->js;
		if(!isset($_GET['debug'])) file_put_contents($this->filePath.'.cache', $this->js);
	}
	
	function checkCached() {
		if(is_file($this->filePath.'.cache')) {
			echo file_get_contents($this->filePath.'.cache');
			exit;
		} else return false;
	}
	
	function parseJS($file, $context) {
		$js = file_get_contents($context.'/'.$file);
		$link = $context.'/'.str_replace(basename($file), '', $file).'constants.yml';
		if(!isset($this->constantsScanned[$link]) && is_file($link)) $this->parseConstants($link);
		preg_match_all('/\/\/= ([a-z]+) ([^\n]+)/', $js, $matches);
		
		foreach($matches[0] as $key => $match) {
			$method = $matches[1][$key].'_command';
			$js = str_replace($matches[0][$key], $this->$method(trim($matches[2][$key]), $context), $js);
		}
		
		return $js;
	}
	
	function stripComments() {
		$this->js = preg_replace('/\/\/([^\n]+)/', '', $this->js);
	}
	
	function require_command($param, $context) {
		if(preg_match('/\"([^\"]+)\"/', $param, $match)) {
			return $this->parseJS(basename($context.'/'.$match[1].'.js'), dirname($context.'/'.$match[1].'.js'));
		} else if(preg_match('/\<([^\>]+)\>/', $param, $match)) {
			return $this->parseJS(basename($context.'/'.$match[1].'.js'), $this->baseJs);
		} else return '';
	}
	
	function provide_command($param, $context) {
		preg_match('/\"([^\"]+)\"/', $param, $match);
		foreach(glob($context.'/'.$match[1].'/*') as $asset) {
			shell_exec('cp -r '.realpath($asset).' '.realpath($this->assetFolder));
		}
	}
	
	function parseConstants($file) {
		$contents = file_get_contents($file);
		preg_match_all('/^([A-Za-z][^\:]+)\:([^\n]+)/', $contents, $matches);
		foreach($matches[0] as $key => $val) {
			$this->constants[$matches[1][$key]] = $matches[2][$key];
		}
		$this->constantsScanned[$file] = true;
	}
	
	function swapConstants() {
		preg_match_all('/\<(\%|\?)\=\s*([^\s|\%|\?]+)\s*(\?|\%)\>/', $this->js, $matches);
		foreach($matches[0] as $key => $replace) {
			$this->js = str_replace($replace, $this->constants[$matches[2][$key]], $this->js);
		}
	}
	
}
$sprocket = new PHPSprocket(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']));