<?php

namespace LibSite;

class ClassesAutoLoader {

	public static function autoloadRecursive($dir, &$result)
	{
	    $realDir = __DIR__ . "/{$dir}";
	    $d = new \DirectoryIterator($realDir);
	    foreach ($d as $fileInfo) {
	        if ($fileInfo->isDot()) {
	            continue;
	        }

	        if ($fileInfo->isDir()) {
	            self::autoloadRecursive($dir . "/" . $fileInfo->getFilename(), $result);
	        }

	        if ($fileInfo->isFile()) {
	            $filename = $fileInfo->getFilename();
	            if (preg_match("#^([A-Z]{1}[A-Za-z0-9]+)\.php$#Ums", $filename, $matches)) {
	                $className = $matches[1];
	                $namesapce = str_replace("/", "\\", $dir);
	                $className = "{$namesapce}\\{$className}";

	                $filePath =  "/local/php_interface/{$dir}/"  . $fileInfo->getFilename();

	                $result[$className] = $filePath;
	            }
	        }
	    }
	}

}


$arAutoloadedClasses = array();
\LibSite\ClassesAutoLoader::autoloadRecursive('LibSite', $arAutoloadedClasses);

\Bitrix\Main\Loader::registerAutoLoadClasses(null, $arAutoloadedClasses);
