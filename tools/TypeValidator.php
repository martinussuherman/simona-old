<?php

class TypeValidator
{
	public static function IsUUIDv4($uuid)
	{
		$UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
		return preg_match($UUIDv4, $uuid);
	}
}