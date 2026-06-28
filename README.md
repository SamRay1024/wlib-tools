# wlib/tools

> **Collection d'outils PHP puissants pour le développement web et applicatif**

Ce package propose une série de classes utilitaires conçues pour simplifier et optimiser votre développement PHP. Que vous ayez besoin de gérer des structures de données hiérarchiques, d'implémenter des patterns de conception avancés ou de créer un système d'extensions flexible, **wlib/tools** vous offre des solutions éprouvées et performantes.


## 🪛 Installation

```bash
composer require wlib/tools
```

**Requirements** : PHP 7.1+ | [wlib/utils](https://github.com/wlib/utils) ^1.1


## 🗂️ Contenu du package

| Classe | Description | Version PHP | Cas d'usage |
|--------|-------------|-------------|-------------|
| [`Singleton`](#-singleton) | Implémentation générique du pattern Singleton | ≥ 5.3 | Création d'instances uniques |
| [`Hooks`](#-hooks) | Système de gestion de hooks/callbacks | ≥ 7.1 | Architecture extensible |
| [`Tree`](#-tree) | Structure de données arborescente dynamique | ≥ 7.1 | Hiérarchies complexes |
| [`TreeSingleton`](#-treesingleton) | Tree en version Singleton | ≥ 7.1 | Arbre global partagé |
| [`TreeConverter`](#-treeconverter) | Convertisseur Tree ↔ JSON | ≥ 7.1 | Persistance des arbres |


## 📚 Documentation

### 🔹 Singleton

> **Classe abstraite de base pour implémenter le pattern Singleton**

La classe `Singleton` fournit une implémentation générique et sécurisée du **pattern Singleton**, garantissant qu'une classe ne peut avoir qu'une seule instance dans toute l'application.

#### **Concepts clés**

- ✅ **Instance unique** : Une seule instance par classe fille
- ✅ **Constructeur protégé** : Empêche l'instanciation directe
- ✅ **Protection anti-clonage** : La méthode `__clone()` lève une exception
- ✅ **Stockage statique** : Les instances sont conservées dans un tableau de classe

#### **Utilisation**

```php
<?php
use wlib\Tools\Singleton;

// 1. Créez votre classe en étendant Singleton
class DatabaseConnection extends Singleton
{
    private $connection;
    
    protected function __construct()
    {
        // Initialisation spécifique
        $this->connection = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
    }
    
    public function query($sql)
    {
        return $this->connection->query($sql);
    }
}

// 2. Utilisez getInstance() pour récupérer l'instance unique
$db = DatabaseConnection::getInstance();
$result = $db->query("SELECT * FROM users");

// 3. Toutes les appels suivants retournent LA MÊME instance
$db2 = DatabaseConnection::getInstance();
// $db === $db2 → true

// ❌ Impossible : constructeur protégé
// $db = new DatabaseConnection();

// ❌ Impossible : clonage interdit
// $db2 = clone $db; // Lève RuntimeException
```

#### **Passage de paramètres**

```php
// Les paramètres sont passés UNIQUEMENT à la première création
class Config extends Singleton
{
    private $settings;
    
    protected function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }
}

// Première appel : paramètres pris en compte
$config = Config::getInstance(['debug' => true, 'env' => 'dev']);

// Appels suivants : même instance, paramètres ignorés
$config2 = Config::getInstance(['debug' => false]); // debug reste true
```

#### **Quand l'utiliser ?**

✅ **Configuration globale**
✅ **Connexion base de données**
✅ **Services partagés** (Logger, Cache, etc.)
✅ **Registry de dépendances**
❌ **Éviter** pour les objets nécessitant plusieurs instances

> 👍 Si la classe `Singleton` peut vous faciliter la vie, si vous vous lancez dans un projet d'envergure, il reste préférable d'éviter ce pattern et de basculer vers un conteneur de dépendances qui vous fournira les instances que vous souhaitez rendre uniques. Voyez du coté de [wlib/dibox](https://github.com/SamRay1024/wlib-dibox), tout y est prévu !

---

### 🔹 Hooks

> **Système de gestion de hooks et callbacks**

La classe `Hooks` permet d'implémenter un **système d'extensions** basé sur des hooks (ou crochets). C'est un mechanism puissants pour ajouter des fonctionnalités sans modifier le code source.

#### **Concepts clés**

- ✅ **Déclaration de hooks** : Créez des points d'extension nommés
- ✅ **Priorités** : Contrôlez l'ordre d'exécution des callbacks
- ✅ **Flexibilité** : Ajoutez/supprimez des hooks dynamiquement
- ✅ **Arguments variables** : Passez des paramètres aux callbacks

#### **Utilisation de base**

```php
<?php
use wlib\Tools\Hooks;

// 1. Déclarer un callback sur un hook
Hooks::add('before_save', function($entity) {
    echo "Préparation de l'entité avant sauvegarde\n";
    $entity->updated_at = time();
});

Hooks::add('before_save', function($entity) {
    echo "Validation de l'entité\n";
}, 20); // Priorité plus élevée = exécuté après

// 2. Exécuter tous les callbacks d'un hook
$user = new User(['name' => 'John']);
Hooks::do('before_save', $user);
// Sortie :
// Préparation de l'entité avant sauvegarde
// Validation de l'entité
```

#### **Gestion des priorités**

```php
// Priorités : plus le nombre est ÉLEVÉ, plus l'exécution est TARDIVE
Hooks::add('process', fn() => echo "Étape 1\n", 10);  // Priorité par défaut
Hooks::add('process', fn() => echo "Étape 2\n", 5);   // Exécuté AVANT
Hooks::add('process', fn() => echo "Étape 3\n", 15);  // Exécuté APRÈS

Hooks::do('process');
// Sortie :
// Étape 2
// Étape 1
// Étape 3
```

#### **Hooks avec plusieurs arguments**

```php
Hooks::add('user_created', function($userId, $userName, $email) {
    envoyerEmailBienvenue($email, $userName);
    logger("Nouvel utilisateur #$userId créé");
});

// Exécution avec arguments
Hooks::do('user_created', 123, 'Alice', 'alice@example.com');
```

#### **Gestion avancée**

```php
// Supprimer un hook et tous ses callbacks
Hooks::remove('before_save');
```

#### **Cas d'usage typiques**

✅ **Plugins WordPress-like**
✅ **Middleware HTTP**
✅ **Événements applicatifs**
✅ **Système de logging extensible**
✅ **Validation de données**

#### **Bonnes pratiques**

```php
// ✅ Bon : Utilisez des noms de hooks descriptifs
Hooks::add('user.registration.validated', $callback);

// ❌ À éviter : Noms trop génériques
Hooks::add('action', $callback); // Peu clair
```

---

### 🌳 Tree

> **Structure de données arborescente dynamique et flexible**

La classe `Tree` permet de créer et manipuler des **structures hiérarchiques** de manière intuitive. Chaque nœud peut contenir des données et des enfants, formant un arbre de profondeur illimitée.

#### **Concepts clés**

- ✅ **Nœuds dynamiques** : Créez des nœuds à la volée
- ✅ **Données flexibles** : Tout type de données peut être stocké
- ✅ **Accès intuitif** : Syntaxe fluide et naturelle
- ✅ **Sérialisation** : Conversion vers tableau et JSON
- ✅ **Persistance** : Chargement/sauvegarde depuis fichiers

#### **Création d'un arbre**

```php
<?php
use wlib\Tools\Tree;

// 1. Création de la racine
$tree = new Tree();

// 2. Ajout de nœuds avec la syntaxe magique
$tree->users()
     ->admins()
     ->data(['id' => 1, 'name' => 'Admin']);

$tree->users()
     ->members()
     ->alice(['id' => 2, 'name' => 'Alice']);

$tree->products()
     ->electronics()
     ->laptops(['count' => 50]);
```

#### **Manipulation des données**

```php
// Ajout/Modification de données
$tree->config()->database('mysql:host=localhost');
$tree->config()->cache(true);

// Récupération des données
$dbConfig = $tree->config->database; // 'mysql:host=localhost'
$cacheEnabled = $tree->config->cache; // true

// Vérification d'existence
if (isset($tree->users->admins)) {
    echo "Le nœud admins existe\n";
}
```

#### **Conversion et export**

```php
// Conversion en tableau
$array = $tree->__toArray();

// Conversion en JSON (via TreeConverter)
$json = json_encode($tree->__toArray(), JSON_PRETTY_PRINT);
```

#### **Gestion des enfants**

```php
// Obtenir les enfants d'un nœud
$children = $tree->users->getChildren(); // ['admins', 'members']
```

#### **Persistance avec fichiers**

```php
// Sauvegarde dans un fichier JSON
$tree->saveToFile('/path/to/tree.json');

// Chargement depuis un fichier JSON
$newTree = new Tree();
$newTree->loadFromFile('/path/to/tree.json');

// Chargement depuis une chaîne
$jsonString = '{"config":{"debug":true}}';
$tree->loadFromString($jsonString);
```

#### **Cas pratiques**

**Exemple 1 : Menu de navigation**
```php
$menu = new Tree();

$menu->home('/');
$menu->about('/about');
$menu->services('/services')
     ->web('Web Development')
     ->mobile('Mobile Apps')
     ->consulting('Consulting');

// Rendu du menu
function renderMenu(Tree $node, $url) {
    $html = '<ul>';
    foreach ($node->getChildren() as $name) {
        $data = $node->$name;
        $active = ($data === $url) ? ' class="active"' : '';
        $html .= "<li$active><a href="$data">$name</a>";
        
        $child = $node->$name();
        if ($child->getChildren()) {
            $html .= renderMenu($child, $url);
        }
        
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}

echo renderMenu($menu, '/services/web');
```

**Exemple 2 : Configuration hiérarchique**
```php
$config = new Tree();

$config->database()
     ->host('localhost')
     ->port(3306)
     ->credentials()
         ->username('admin')
         ->password('secret');

// Accès aux valeurs
$dbHost = $config->database->host; // 'localhost'
```

**Exemple 3 : Structure de fichiers**
```php
$fileSystem = new Tree();

$fileSystem->src()
     ->Controllers()
         ->UserController('UserController.php')
         ->ProductController('ProductController.php');
```

---

### 🌳 TreeSingleton

> **Version Singleton de Tree pour un arbre global partagé**

`TreeSingleton` combine les fonctionnalités de `Tree` avec le pattern `Singleton`, permettant d'avoir un **arbre unique accessible partout dans l'application**.

#### **Quand l'utiliser ?**

- Configuration globale de l'application
- Registry de services
- Cache hiérarchique
- Structure de données partagée entre composants

#### **Utilisation**

```php
<?php
use wlib\Tools\TreeSingleton;

// 1. Étendez TreeSingleton
class AppConfig extends TreeSingleton {}

// 2. Initialisez l'arbre global
AppConfig::getInstance()
    ->database()
        ->host('localhost')
        ->user('root');

// 3. Accédez depuis n'importe où dans l'application
$dbHost = AppConfig::getInstance()->database->host;

// 4. Persistance
AppConfig::getInstance()->saveToFile('/config/app_config.json');

// 5. Chargement au démarrage
AppConfig::getInstance()->loadFromFile('/config/app_config.json');
```

#### **Comparaison Tree vs TreeSingleton**

| Fonctionnalité | Tree | TreeSingleton |
|---------------|------|---------------|
| Création multiple | ✅ Oui | ❌ Non (1 instance) |
| Accès global | ❌ Non | ✅ Oui |
| Sérialisation | ✅ Oui | ✅ Oui |
| Persistance | ✅ Oui | ✅ Oui |
| Utilisation | Locale | Globale |

---

### ⚙️ TreeConverter

> **Convertisseur universel pour les arbres Tree**

`TreeConverter` fournit des méthodes statiques pour **exporter et importer** des structures Tree vers/d'après différents formats (actuellement JSON).

#### **Fonctionnalités**

- ✅ Export Tree → JSON
- ✅ Import JSON → Tree
- ✅ Gestion des erreurs
- ✅ Extensible à d'autres formats

#### **Utilisation directe**

```php
<?php
use wlib\Tools\Tree;
use wlib\Tools\TreeConverter;

$tree = new Tree();
$tree->root()->child1('data1');

// Export manuel
$json = TreeConverter::export($tree, 'json');

// Import manuel
$newTree = new Tree();
$result = TreeConverter::import($json, $newTree, 'json');

// Gestion des erreurs
if ($result === false) {
    $error = TreeConverter::getLastError();
    echo "Erreur de conversion: $error\n";
}
```


## 🎯 Cas d'usage avancés

### Architecture modulaire avec Hooks

```php
// Module 1 : Core
class UserService
{
    public function createUser($data)
    {
        $user = new User($data);
        
        // Déclencher les hooks avant sauvegarde
        Hooks::do('user.before_create', $user, $data);
        
        $user->save();
        
        // Déclencher les hooks après sauvegarde
        Hooks::do('user.after_create', $user);
        
        return $user;
    }
}

// Module 2 : Plugin de validation
Hooks::add('user.before_create', function($user, $data) {
    if (empty($data['email'])) {
        throw new Exception("Email obligatoire");
    }
}, 10);

// Module 3 : Plugin de logging
Hooks::add('user.after_create', function($user) {
    Logger::info("Utilisateur créé: {$user->id}");
}, 5);
```

### Système de configuration multi-niveaux

```php
class Config extends TreeSingleton
{
    public static function loadFromFiles(array $files)
    {
        $config = self::getInstance();
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $config->loadFromFile($file);
            }
        }
        
        return $config;
    }
}

// Chargement de la configuration
Config::loadFromFiles([
    '/config/defaults.json',
    '/config/local.json',
    '/config/environment.json'
]);
```

### Cache structuré

```php
class CacheTree extends TreeSingleton
{
    public static function get($key, $default = null)
    {
        $cache = self::getInstance();
        $keys = explode('.', $key);
        
        $current = $cache;
        foreach ($keys as $k) {
            if (!isset($current->$k)) {
                return $default;
            }
            $current = $current->$k();
        }
        
        return $current->data();
    }
    
    public static function set($key, $value)
    {
        $cache = self::getInstance();
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        
        $current = $cache;
        foreach ($keys as $k) {
            $current = $current->$k();
        }
        
        $current->$lastKey($value);
    }
}

// Utilisation
CacheTree::set('user.123.profile', ['name' => 'Alice']);
$profile = CacheTree::get('user.123.profile');
```


## 🔧 Bonnes pratiques

### Pour Singleton

1. **✅ Utilisez des noms explicites**
   ```php
   // Bon
   class DatabaseManager extends Singleton {}
   
   // Moins bon
   class DB extends Singleton {}
   ```

2. **✅ Documentez le singleton**

3. **❌ Évitez l'abus de singletons**

### Pour Hooks

1. **✅ Utilisez un système de nommage hiérarchique**
   ```php
   Hooks::add('user.registration.before_validation', $callback);
   ```

2. **✅ Documentez vos hooks**

3. **✅ Gérez les priorités avec soin**

4. **✅ Nettoyez vos hooks**
   ```php
   // Dans les tests unitaires
   Hooks::remove('test_hook');
   ```

### Pour Tree

1. **✅ Utilisez des noms de nœuds significatifs**

2. **✅ Préférez Tree pour les hiérarchies complexes**

3. **✅ Utilisez TreeSingleton pour les configurations globales**

4. **✅ Validez les données avant import**


## 📊 Tableau récapitulatif des méthodes

### Méthodes par Classe

#### Singleton
| Méthode | Description | Retour |
|---------|-------------|--------|
| `getInstance(array $args = [])` | Récupère l'instance unique | `object` |
| `__clone()` | Interdit le clonage | `void` (lève `RuntimeException`) |

#### Hooks
| Méthode | Description | Paramètres |
|---------|-------------|------------|
| `add(string $name, callable $callback, int $priority = 10)` | Ajoute un callback à un hook | Nom, callback, priorité |
| `remove(string $name)` | Supprime un hook et ses callbacks | Nom du hook |
| `do(string $name, ...$args)` | Exécute tous les callbacks d'un hook | Nom + arguments |

#### Tree
| Méthode | Description | Retour |
|---------|-------------|--------|
| `__call($name, $args)` | Crée/accède à un nœud | `self` |
| `__get($name)` | Récupère les données d'un nœud | `mixed|null` |
| `__isset($name)` | Vérifie l'existence d'un nœud | `bool` |
| `__unset($name)` | Supprime un nœud | `void` |
| `getChildren()` | Liste les enfants | `array` |
| `data()` | Récupère les données du nœud courant | `mixed` |
| `loadFromFile($file, $format = 'json')` | Charge depuis un fichier | `bool|null` |
| `loadFromString($content, $format = 'json')` | Charge depuis une chaîne | `bool` |
| `saveToFile($file, $format = 'json')` | Sauvegarde dans un fichier | `bool|null` |
| `__toArray()` | Convertit en tableau | `array` |
| `__toString()` | Convertit en chaîne (sérialisée) | `string` |

#### TreeSingleton
| Méthode | Description | Retour |
|---------|-------------|--------|
| `getInstance()` | Récupère l'instance unique (héritée) | `self` |
| Toutes les méthodes de Tree | Mêmes fonctionnalités | - |

#### TreeConverter
| Méthode | Description | Paramètres |
|---------|-------------|------------|
| `export(Tree $tree, string $format = 'json')` | Exporte un arbre | Arbre, format |
| `import($data, Tree $tree, string $format = 'json')` | Importe dans un arbre | Données, arbre, format |
| `getLastError()` | Récupère la dernière erreur | - |

## 🎓 Concepts Théoriques

### Le Pattern Singleton

Le **Singleton** est un pattern de conception (Design Pattern) qui garantit qu'une classe n'a qu'une seule instance et fournit un point d'accès global à cette instance.

**Avantages :**
- ✅ Contrôle strict de l'instanciation
- ✅ Accès global simple
- ✅ Réduction de l'utilisation mémoire

**Inconvénients :**
- ⚠️ Peut violer le principe de responsabilité unique
- ⚠️ Difficile à tester (état global) → ([Pourquoi éviter l'état global ?](https://doc.nette.org/fr/dependency-injection/global-state))
- ⚠️ Peut masquer des dépendances

### Le Pattern Hook/Callback

Le système de **Hooks** implémente le **pattern Observer** sous une forme simplifiée.

**Avantages :**
- ✅ Extensibilité sans modification du code
- ✅ Découplage fort entre composants
- ✅ Flexibilité maximale


## 🔍 Dépannage

### Erreurs courantes

#### Singleton
```
Problème : "Call to protected constructor"
Solution : Utilisez getInstance() au lieu de new

Problème : "Singletons can't be cloned"
Solution : Ne clonez pas un Singleton
```

#### Hooks
```
Problème : Les callbacks ne sont pas appelés
Solution : Vérifiez le nom du hook (respectez la casse)

Problème : Ordre d'exécution incorrect
Solution : Vérifiez les priorités
```

#### Tree
```
Problème : Nœud introuvable
Solution : Vérifiez la casse du nom. Utilisez isset() avant accès

Problème : Données perdues lors de la conversion JSON
Solution : Les données sont stockées dans '__data' pour les nœuds avec enfants
```

## 📜 Licence

Ce package est distribué sous la **licence CeCILL 2.1**, une licence open source française compatible avec la GPL.

> **CeCILL** (CEA CNRS INRIA Logiciel Libre) est une licence qui garantit la liberté d'utiliser, modifier et redistribuer le logiciel.

Pour plus d'informations : [http://www.cecill.info](http://www.cecill.info)

## 🤝 Contribution

Les contributions sont les bienvenues !

### Axes d'amélioration
- [ ] Ajouter des tests unitaires complets
- [ ] Implémenter d'autres formats dans TreeConverter (XML, YAML)
- [ ] Ajouter la validation des données dans Tree
- [ ] Implémenter la recherche de nœuds par chemin dans Tree
- [ ] Ajouter des méthodes de parcours (DFS, BFS)


## 🚀 Pour aller plus loin

### Combinaisons puissantes

```php
// 1. Singleton + Tree = Configuration globale
class AppConfig extends TreeSingleton {}

// 2. Hooks + Tree = Système d'événements hiérarchique
Hooks::add('config.loaded', function($configTree) {
    $configTree->app()->debug(true);
});

// 3. Tree + Persistance = Cache structuré
$cache = new Tree();
$cache->loadFromFile('/tmp/app_cache.json');
```

### Patterns complémentaires

- **Factory** : Pour créer des instances Tree pré-remplies
- **Decorator** : Pour ajouter des fonctionnalités aux nœuds Tree
- **Strategy** : Pour implémenter différents algorithmes de parcours
- **Composite** : Tree implémente naturellement ce pattern


## ✨ Conclusion

Le package **wlib/tools** vous offre un ensemble d'outils puissants et flexibles pour structurer votre code PHP. Que vous ayez besoin de centraliser vos ressources, étendre votre application, organiser vos données ou partager une configuration, ces classes permettent de résoudre des problèmes complexes de manière élégante et maintenable.

**Commencez dès aujourd'hui à simplifier votre développement PHP !** 🚀