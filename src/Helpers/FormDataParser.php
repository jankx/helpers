<?php
namespace Jankx\Helpers;

class FormDataParser
{
	public function parse($stream, $boundary = null)
	{
		$return = array('variables' => array(), 'files' => array());

		$partInfo = null;

		while(($lineN = fgets($stream)) !== false)
		{
			if(strpos($lineN, '--') === 0)
			{
				if(!isSet($boundary) || $boundary == null)
				{
					$boundary = rtrim($lineN);
				}
				continue;
			}

			$line = rtrim($lineN);

			if($line == '')
			{
				if(!empty($partInfo['Content-Disposition']['filename']))
				{
					$this->parse_file($stream, $boundary, $partInfo, $return['files']);
				}
				elseif(!$partInfo != null)
				{
					$this->parse_variable($stream, $boundary, $partInfo['Content-Disposition']['name'], $return['variables']);
				}
				$partInfo = null;
				continue;
			}

			$delim = strpos($line, ':');

			$headerKey = substr($line, 0, $delim);
			$headerVal = ltrim($line, $delim + 1);

			$partInfo[$headerKey] = $this->parse_header_value($headerVal, $headerKey);
		}

		fclose($stream);
		return $return;
	}

	public function parse_header_value($line, $header = '')
	{
		$retval = array();
		$regex  = '/(^|;)\s*(?P<name>[^=:,;\s"]*):?(=("(?P<quotedValue>[^"]*(\\.[^"]*)*)")|(\s*(?P<value>[^=,;\s"]*)))?/mx';

		$matches = null;
		preg_match_all($regex, $line, $matches, PREG_SET_ORDER);

		for($i = 0; $i < count($matches); $i++)
		{
			$match = $matches[$i];
			$name = $match['name'];
			$quotedValue = $match['quotedValue'];
			if(empty($quotedValue))
			{
				$value = $match['value'];
			}
			else {
				$value = stripcslashes($quotedValue);
			}
			if($name == $header && $i == 0)
			{
				$name = 'value';
			}
			$retval[$name] = $value;
		}
		return $retval;
	}

	public function parse_variable($stream, $boundary, $name, &$array)
	{
		$fullValue = '';
		$lastLine = null;
		while(($lineN = fgets($stream)) !== false && strpos($lineN, $boundary) !== 0)
		{
			if($lastLine != null)
			{
				$fullValue .= $lastLine;
			}
			$lastLine = $lineN;
		}

		if($lastLine != null)
		{
			$fullValue .= rtrim($lastLine, '\r\n');
		}
		$array[$name] = $fullValue;

	}

	public function parse_file($stream, $boundary, $info, &$array)
	{
		$tempdir = sys_get_temp_dir();

		$name = $info['Content-Disposition']['name'];
		$fileStruct['name'] = $info['Content-Disposition']['filename'];
		$fileStruct['type'] = $info['Content-Type']['value'];

		$array[$name] = &$fileStruct;

		if(empty($tempdir))
		{
			$fileStruct['error'] = UPLOAD_ERR_NO_TMP_DIR;
			return;
		}

		$tempname = tempnam($tempdir, 'php_upl');
		$outFP = fopen($tempname, 'wb');
		if($outFP === false)
		{
			$fileStruct['error'] = UPLOAD_ERR_CANT_WRITE;
			return;
		}

		$lastLine = null;
		while(($lineN = fgets($stream, 4096)) !== false)
		{
			if($lastLine != null)
			{
				if(strpos($lineN, $boundary) === 0) break;
				if(fwrite($outFP, $lastLine) === false)
				{
					$fileStruct = UPLOAD_ERR_CANT_WRITE;
					return;
				}
			}
			$lastLine = $lineN;
		}

		if($lastLine != null)
		{
			if(fwrite($outFP, rtrim($lastLine, '\r\n')) === false)
			{
				$fileStruct['error'] = UPLOAD_ERR_CANT_WRITE;
				return;
			}
		}
		$fileStruct['error'] = UPLOAD_ERR_OK;
		$fileStruct['size'] = filesize($tempname);
		$fileStruct['tmp_name'] = $tempname;
	}
}
