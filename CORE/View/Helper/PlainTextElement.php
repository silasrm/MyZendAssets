<?php

class CORE_View_Helper_PlainTextElement extends Zend_View_Helper_FormElement
{
	public function PlainTextElement($name, $value = null, $attribs = null)
	{
		$info = $this->_getInfo($name, $value, $attribs);
		
		extract($info); // name, value, attribs, options, listsep, disable
		if (null === $value) {$value = $name;}

		return $value;
	}
}