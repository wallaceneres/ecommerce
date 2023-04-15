<?php

namespace Hcode;

class Model
{
//array to save fields to be used in setters and getters
	private $values = [];

	public function __call($name, $args)
	{
		//get the 3 first letters of the var name to identify that is get or set
		$method = substr($name, 0, 3);
		//get the rest of the var name to identify the name of the getter or setter.
		$fieldName = substr($name, 3, strlen($name));

		switch ($method)
		{

			case "get":
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;

			case "set":
				$this->values[$fieldName]= $args[0];
				break;

		}
	}

	public function setData($data = array())
	{
		foreach ($data as $key => $value)
		{
			$this->{"set".$key}($value);
		}

	}

	public function getValues()
	{
		return $this->values;
	}

}