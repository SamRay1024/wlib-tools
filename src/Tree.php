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

use phpDocumentor\Reflection\Types\Boolean;

/**
 * Build dynamic trees with this class.
 *
 * Result tree will be composed by nodes. Each node can have child nodes and/or
 * a data.
 *
 * @author Cédric Ducarre
 * @since 22/12/2009
 * @version 07/07/2011
 * @package wlib
 */
class Tree
{
	/**
	 * Data container of the node.
	 * @var mixed
	 */
	private $mData = null;

	/**
	 * Child nodes array.
	 * @var array
	 */
	private $aNodes = [];

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		unset($this->aNodes);
		unset($this->mData);
	}

	/**
	 * Get the serialized string of instance.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return serialize($this);
	}

	/**
	 * Convert the current tree to array.
	 *
	 * A node which contains children and data sees its data placed in the 
	 * '__data' element of the assiocative array which will be assigned to it.
	 *
	 * @return array
	 */
	public function __toArray()
	{
		$aTree = array();

		foreach ($this->aNodes as $sNode => $mNode)
		{
			$aTree[$sNode] = array();

			if (sizeof($mNode->aNodes) > 0)
			{
				if (!is_null($mNode->mData))
					$aTree[$sNode]['__data'] = $mNode->mData;

				$aTree[$sNode] = array_merge($aTree[$sNode], $mNode->__toArray());
			}

			else $aTree[$sNode] = $mNode->mData;
		}

		return $aTree;
	}

	/**
	 * Handle nodes creating and accesses.
	 * 
	 * - Create : `$tree->node()`, only if node doesn't exists,
	 * - Create with data : `$tree->node('data')`, 
	 * - Get node : `$node = $tree->node()`,
	 * - Get data : `$data = $tree->node;`.
	 *
	 * @param string $sName	Node name.
	 * @param array $aArgs Input parameters.
	 * @return self Called node instance.
	 */
	public function __call($sName, array $aArgs)
	{
		$bGet	= (sizeof($aArgs) <= 0);

		$bIsSet	= isset($this->aNodes[$sName]);

		if (!$bIsSet)
			$this->aNodes[$sName] = new self;

		if (!$bGet)
			$this->aNodes[$sName]->mData = $aArgs[0];

		return $this->aNodes[$sName];
	}

	/**
	 * Get data from node.
	 * 
	 * Example :
	 * 
	 * `$data = $tree->node()->subNode;`
	 *
	 * @return mixed|null
	 */
	public function __get($sName): mixed
	{
		if (!isset($this->aNodes[$sName]))
			return null;

		return $this->aNodes[$sName]->mData;
	}

	/**
	 * Check if node exists.
	 *
	 * @param string $sName	Node name.
	 * @return boolean
	 */
	public function __isset($sName): bool
	{
		return isset($this->aNodes[$sName]);
	}

	/**
	 * Delete a node.
	 *
	 * @param string $sName Node name.
	 */
	public function __unset($sName)
	{
		unset($this->aNodes[$sName]);
	}

	/**
	 * Get nodes children names of current node.
	 *
	 * @return array
	 */
	public function getChildren(): array
	{
		return array_keys($this->aNodes);
	}

	/**
	 * Other way to get node data.
	 * 
	 * @return mixed
	 */
	public function data(): mixed
	{
		return $this->mData;
	}

	/**
	 * Load tree from a file.
	 *
	 * @param string $sFileName Address of the file to load.
	 * @param string $sFormat File format (JSON by default).
	 * @return boolean|null `false` in case of error, `null` for unknown format.
	 */
	public function loadFromFile($sFileName, $sFormat = 'json'): bool|null
	{
		$sFileContent = fileGetContent($sFileName);

		return ($sFileContent !== false
			? $this->loadFromString($sFileContent, $sFormat)
			: false
		);
	}

	/**
	 * Load the tree from a string.
	 *
	 * @param string $sContent Content string to load.
	 * @param string $sFormat String format (JSON by default).
	 * @return boolean `false` in case of error.
	 */
	public function loadFromString($sContent, $sFormat = 'json'): bool
	{
		return TreeConverter::import($sContent, $this, $sFormat);
	}

	/**
	 * Save the tree in a file.
	 *
	 * @param string $sFileName File address.
	 * @param string $sFormat File format (JSON by default).
	 * @return boolean|null `false` in case of error, `null` for unknown format.
	 */
	public function saveToFile($sFileName, $sFormat = 'json'): bool|null
	{
		return FilePutContent(
			$sFileName,
			TreeConverter::export($this, $sFormat)
		);
	}
}
