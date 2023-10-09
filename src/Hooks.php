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

/**
 * Handle hooks in your code.
 *
 * @author Cédric Ducarre
 * @since 23/04/2023
 * @version 23/04/2023
 * @package wlib
 */
class Hooks
{
	/**
	 * Declared hooks.
	 * @array
	 */
	private static $aHooks = [];

	/**
	 * Attach a callback function to a hook name.
	 * 
	 * Hooks assignes to the same priority will be executed in order of attachment.
	 * 
	 * @param string $sName Hook name.
	 * @param callable $cbCallback Callback function.
	 * @param integer $iPriority Execution priority, 10 by default.
	 */
	public static function add(string $sName, callable $cbCallback, int $iPriority = 10)
	{
		if (is_callable($cbCallback))
			self::$aHooks[$sName][$iPriority][] = $cbCallback;
	}

	/**
	 * Delete a hook.
	 * 
	 * @param string $sName Hook name.
	 */
	public static function remove(string $sName)
	{
		if (isset(self::$aHooks[$sName]))
			unset(self::$aHooks[$sName]);
	}
	
	/**
	 * Execute callback functions attached to the given hook.
	 * 
	 * @param string $sName Hook name.
	 * @param mixed $args Arguments passed to the callback functions.
	 */
	public static function do($sName, ...$args)
	{
		if (isset(self::$aHooks[$sName]))
		{
			foreach (self::$aHooks[$sName] as $aCallbacks)
			{
				foreach ($aCallbacks as $cbCallback)
				{
					call_user_func($cbCallback, ...$args);
				}
			}
		}
	}
}