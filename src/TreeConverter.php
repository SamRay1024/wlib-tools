<?php

/* ==== LICENCE AGREEMENT =====================================================
 *
 * © Cédric Ducarre (20/05/2010)
 * 
 * wlib is a set of tools aiming to help in PHP web developpement.
 * 
 * This software is governed by the CeCILL license under French law and
 * abiding by the rules of distribution of free software. You can use, 
 * modify and/or redistribute the software under the terms of the CeCILL
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 * 
 * As a counterpart to the access to the source code and rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty and the software's author, the holder of the
 * economic rights, and the successive licensors have only limited
 * liability.
 * 
 * In this respect, the user's attention is drawn to the risks associated
 * with loading, using, modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean that it is complicated to manipulate, and that also
 * therefore means that it is reserved for developers and experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or 
 * data to be ensured and, more generally, to use and operate it in the 
 * same conditions as regards security.
 * 
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 * 
 * ========================================================================== */

namespace wlib\Tools;

use phpDocumentor\Reflection\PseudoTypes\False_;

/**
 * Convert Trees from/to a given format.
 *
 * @author Cédric Ducarre
 * @since 22/12/2009
 * @version 07/07/2011
 * @package wlib
 */
class TreeConverter
{	
	/**
	 * Last convert error.
	 * @var mixed
	 */
	static private $mConvertError = null;
	
	/**
	 * Export a tree to the given format.
	 * 
	 * Return `false` in case of error or `NULL` if format is unknown.
	 * 
	 * @param Tree $oTree Tree instance to export.
	 * @param string $sFormat Destination format ('json' by default).
	 * @return mixed|false|null
	 */
	static public function export(Tree &$oTree, string $sFormat = 'json'): mixed
	{
		switch ($sFormat)
		{
			case 'json':	return self::_exportToJson($oTree);
			default: 		return null;
		}
	}

	/**
	 * Import a tree from given data in given format.
	 * 
	 * Return `false` in case of error or `NULL` if format is unknown.
	 *
	 * @param mixed $mData Data to import.
	 * @param Tree $oTree Tree instance to populate.
	 * @param string $sFormat Import format ('json' by default).
	 * @return boolean|null Boolean of `null` for unsupported format.
	 */
	static public function import($mData, Tree &$oTree, $sFormat = 'json')
	{
		switch ($sFormat)
		{
			case 'json' :	return self::_importFromJson($mData, $oTree);
			default:		return null;
		}
	}
	
	/**
	 * Get the last error.
	 *
	 * @return mixed Code ou message d'erreur.
	 */
	static public function getLastError(): mixed
	{
		return self::$mConvertError;
	}
	
	/**
	 * Get the constant name of the last JSON error.
	 *
	 * @return string
	 */
	static private function _getLastJsonError(): string
	{
		$aConstants = get_defined_constants(true);
		$aJsonErrors = array();
			
		foreach ($aConstants['json'] as $name => $value)
			if (!strncmp($name, 'JSON_ERROR_', 11))
				$aJsonErrors[$value] = $name;
	
		return $aJsonErrors[json_last_error()];
	}
	
	/**
	 * Export to JSON.
	 *
	 * @param Tree $oTree Tree to export.
	 * @return string|false
	 */
	static private function _exportToJson(Tree &$oTree): string|false
	{
		$json = json_encode($oTree->__toArray());
		
		// Si erreur
		if (  $json == 'null')
		{
			self::$mConvertError = self::_getLastJsonError();
			return false;
		}
		
		return $json;
	}
	
	/**
	 * Import data from JSON.
	 *
	 * @param string $sJsonData JSON content.
	 * @param Tree $oTree Destination tree.
	 * @return boolean
	 */
	static private function _importFromJson($sJsonData, Tree &$oTree): bool
	{
		$oJson = json_decode($sJsonData);
		
		if (is_null($oJson))
		{
			self::$mConvertError = self::_getLastJsonError();
			return false;
		}
		
		self::_importFromJsonObject($oJson, $oTree);
		
		return true;
	}
	
	/**
	 * Recursive import of JSON instance.
	 *
	 * @param stdClass $oJson Current JSON instance.
	 * @param Tree $oTree Current imported node.
	 */
	static private function _importFromJsonObject($oJson, Tree &$oTree )
	{
		foreach ($oJson as $sNodeName => $mNodeValue)
		{
			if ($sNodeName == '__data')
				continue;
		
			if (is_object($mNodeValue))
			{
				isset($mNodeValue->__data)
					? $oTree->$sNodeName($mNodeValue->__data)
					: $oTree->$sNodeName();
				
				// Needed to avoid strict error
				$oChildNode = $oTree->$sNodeName();
				
				self::_importFromJsonObject($mNodeValue, $oChildNode);
			}
			else $oTree->$sNodeName($mNodeValue);
		}
	}
}