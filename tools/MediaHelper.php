<?php

class MediaHelper
{
	public static function FileResponse($filename)
	{
		$response = new Phalcon\Http\Response();
		$response->setContentType(mime_content_type($filename));
		$response->setHeader('Content-Disposition', 'attachment; filename="'.basename($filename).'"');
		$response->setContent(file_get_contents($filename));
		return $response;
	}

	public static function FileResponseCustom($filename, $mime, $content)
	{
		$response = new Phalcon\Http\Response();
		$response->setContentType($mime);
		$response->setHeader('Content-Disposition', 'attachment; filename="'.basename($filename).'"');
		$response->setContent($content);
		return $response;
	}

}