<?php

class Utils
{
	const TEMPLATE_DIR = BASE_DIR . '/template';
	const UPLOAD_DIR = BASE_DIR . '/upload';
	const MESSAGE_ERROR = 'error';
	const MESSAGE_NOT_FOUND = 'not-found';
	const MESSAGE_SUCCESS = 'success';

	public static function DateTimeNow()
	{
		return (new DateTime('NOW'))->format('Y-m-d H:i:s');
	}

	public static function CreateDir($dirname)
	{
		if (!file_exists($dirname))
		{
			// TODO: prevent racing, lock?
			$tmp_umask = umask(0);
			mkdir($dirname, 0775, true);
			umask($tmp_umask);
		}
	}
}