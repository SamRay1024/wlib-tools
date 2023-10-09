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

use wlib\Sys\File;

/**
 * Classe TreeSingleton
 *
 * Construire un WTree unique.
 * Le code de cette classe est la copie conforme de celui de la classe WTree.
 *
 * @author Cédric Ducarre
 * @since 06/07/2011
 * @version 07/07/2011
 * @package wlib
 */
class TreeSingleton extends Singleton
{
	/**
	 * Conteneur de données du noeud.
	 *
	 * @var mixed
	 * @access private
	 */
	private $_mData = null;
	
	/**
	 * Tableau des noeuds enfants.
	 *
	 * @var array
	 * @access private
	 */
	private $_aNodes = array();
	
	/**
	 * Destructeur.
	 */
	public function __destruct() { 
	
		unset($this->_aNodes);
		unset($this->_mData);
	}

	/**
	 * Obtenir la chaîne sérialisée de l'instance.
	 *
	 * @return string
	 */
	public function __toString() { return serialize($this); }
	
	/**
	 * Convertir l'objet en tableau.
	 *
	 * Un noeud qui contient des enfants et une donnée voit sa donnée placée dans l'élément
	 * '__data' du tableau associatif qui lui sera affecté.
	 *
	 * @return array
	 */
	public function __toArray() {
	
		$aTree = array();
	
		// Parcours des noeuds enfants
		foreach( $this->_aNodes as $sNode => $mNode ) {
		
			$aTree[$sNode] = array();
				
			// Si le noeud courant possède des enfants
			if( sizeof($mNode->_aNodes) > 0 ) {
			
				// Stockage de la donnée éventuelle du noeud courant
				if( !is_null($mNode->_mData) )
					$aTree[$sNode]['__data'] = $mNode->_mData;
			
				// Fusion des noeuds enfants (la récursivité démarre ici)
				$aTree[$sNode] = array_merge($aTree[$sNode], $mNode->__toArray());
			}
			
			// Sinon, stockage simple de la donnée du noeud
			else $aTree[$sNode] = $mNode->_mData;
		}
		
		return $aTree;
	}

	/**
	 * Méthode magique pour traiter l'appel de méthodes dynamiques.
	 *
	 * A chaque appel, le statut de l'arbre est remis à NULL.
	 *
	 * @param string $sName	Nom de la méthode appelée.
	 * @param array $aArgs	Paramètres d'entrée.
	 * @return Wtree Instance du noeud appelé.
	 */
	public function __call( $sName, array $aArgs ) {

		// S'agit-il d'un accès ou d'une écriture ?
		$bGet	= (sizeof($aArgs) <= 0);

		// Est-ce que le noeud existe ?
		$bIsSet	= isset($this->_aNodes[$sName]);

		// Noeud inexistant => création
		if( !$bIsSet )
			$this->_aNodes[$sName] = new self;

		// Ecriture d'une donnée dans le noeud
		if( !$bGet )
			$this->_aNodes[$sName]->_mData = $aArgs[0];

		// Retour du noeud demandé
		return $this->_aNodes[$sName];
	}
	
	/**
	 * Obtenir la donnée d'un noeud.
	 *
	 * Si le noeud n'existe pas ou qu'il ne contient aucune donnée,
	 * la valeur NULL est retournée.
	 *
	 * @return null | mixed
	 */
	public function __get( $sName ) {
	
		if( !isset($this->_aNodes[$sName]) )
			return null;
			
		return $this->_aNodes[$sName]->_mData;
	}

	/**
	 * Vérifier l'existence d'un noeud.
	 *
	 * @param string $sName	Nom du noeud.
	 * @return boolean
	 */
	public function __isset( $sName ) { return isset($this->_aNodes[$sName]); }

	/**
	 * Supprimer un noeud.
	 *
	 * @param string $sName Nom du noeud à supprimer.
	 */
	public function __unset( $sName ) { unset($this->_aNodes[$sName]); }

	/**
	 * Obtenir les noms des noeuds enfants du noeud courant.
	 *
	 * @return array
	 */
	public function getChildren() { return array_keys($this->_aNodes); }
	
	/**
	 * Charger l'arbre depuis un fichier.
	 *
	 * @param string $sFileName Adresse du fichier.
	 * @param string $sFormat Format du fichier (json par défaut).
	 * @return boolean|null False en cas d'erreur ou null si le format n'est pas supporté.
	 */
	public function loadFromFile( $sFileName, $sFormat = 'json' ) {
	
		$sFileContent = File::getContent($sFileName);
		
		return ( $sFileContent !== false 
			? $this->loadFromString($sFileContent)
			: false
		);
	}
	
	/**
	 * Charger l'arbre depuis une chaîne.
	 *
	 * @param string $sContent Contenu à charger.
	 * @param string $sFormat Format de la chaîne (json par défaut).
	 * @return boolean False en cas d'erreur.
	 */
	public function loadFromString( $sContent, $sFormat = 'json' ) {
	
		return TreeConverter::import($sContent, $this, $sFormat);
	}
	
	/**
	 * Enregistrer l'arbre dans un fichier.
	 *
	 * @param string $sFileName Adresse du fichier.
	 * @param string $sFormat Format du fichier (json par défaut).
	 * @return boolean|null False en cas d'erreur ou null si le format n'est pas supporté.
	 */
	public function saveToFile( $sFileName, $sFormat = 'json' ) {
	
		return File::putContent(
			$sFileName,
			TreeConverter::export($this, $sFormat)
		);
	}
}