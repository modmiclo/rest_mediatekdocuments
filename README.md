# rest_mediatekdocuments

## Dépôt d’origine

Cette API est basée sur la structure de l’API CNED suivante :  
https://github.com/CNED-SLAM/rest_mediatekdocuments

Le readme du dépôt d’origine décrit l’architecture générale de l’API (rôle des fichiers, fonctionnement du routeur, format des requêtes, etc.).  
Ce dépôt présente uniquement les évolutions ajoutées pour répondre aux besoins de l’application MediaTekDocuments.

---

## Objectif de ce dépôt

Cette API REST (PHP) permet d’exécuter des requêtes sur la base MySQL `mediatek86` afin de répondre aux fonctionnalités ajoutées dans l’application WinForms MediaTekDocuments :

- gestion des documents  
- gestion des commandes  
- gestion des abonnements  
- gestion des exemplaires  
- authentification des utilisateurs  

Application cliente :  
https://github.com/modmiclo/mediatekdocuments

---

## Principales évolutions réalisées

Les évolutions concernent principalement :

- `.env` : centralisation des informations sensibles (accès BDD, authentification API)
- `MyAccessBDD.php` : ajout de fonctions SQL spécifiques (jointures, contraintes métier, transactions)
- `.htaccess` : sécurisation de l’accès direct à la racine de l’API (retour HTTP 400)
- amélioration de la robustesse : remplacement de `filter_input` lorsque nécessaire

---

## Fonctionnalités ajoutées

### 1) Accès optimisé aux tables simples

Ajout d’une fonction permettant de récupérer les valeurs de référence triées par libellé :

- `genre`
- `public`
- `rayon`
- `etat`

Objectif : alimenter les listes déroulantes côté application et éviter la saisie libre.

---

### 2) Récupération des documents avec jointures

Ajout de requêtes dédiées permettant de récupérer des listes complètes directement exploitables par l’application :

- récupération de tous les livres avec informations associées
- récupération de tous les DVD avec informations associées
- récupération de toutes les revues avec informations associées

Ces requêtes utilisent des jointures afin de retourner notamment les libellés (genre, public, rayon).

---

### 3) Gestion CRUD des documents avec transactions

Ajout de traitements spécifiques pour insérer, modifier et supprimer :

- Livre : écritures multi-tables (`document`, `livres_dvd`, `livre`)
- DVD : écritures multi-tables (`document`, `livres_dvd`, `dvd`)
- Revue : écritures multi-tables (`document`, `revue`)

Les opérations sont gérées via transactions afin de garantir le principe "tout ou rien".

Contraintes métier appliquées :

- modification : l’identifiant n’est pas modifiable
- suppression : refus si dépendances (exemplaires, commandes, abonnements)

---

### 4) Gestion des commandes de livres et DVD

Ajouts nécessaires à la gestion des commandes et du suivi :

- table `suivi` (étapes) et association avec les commandes
- règles de progression (impossible de revenir à une étape précédente)
- interdiction de passer à "réglée" si la commande n’est pas "livrée"
- suppression interdite si la commande est livrée
- génération automatique des exemplaires lors du passage en "livrée"

---

### 5) Gestion des abonnements de revues et alerte "fin proche"

Ajouts dédiés aux abonnements :

- récupération des abonnements d’une revue (y compris expirés)
- suppression encadrée (refus si des parutions existent sur la période)
- récupération des abonnements "fin proche" (moins de 30 jours) pour l’alerte au démarrage

---

### 6) Gestion des exemplaires et de leur état

Ajouts permettant :

- récupération des exemplaires (livres, DVD, revues) avec libellé d’état (jointure avec `etat`)
- modification de l’état d’un exemplaire
- suppression d’un exemplaire (avec contrôles d’existence et contraintes d’intégrité)

---

### 7) Authentification applicative (utilisateurs et services)

Ajouts permettant à l’application de gérer les droits selon le service :

- tables `service` et `utilisateur`
- requête d’authentification (login / mot de passe)
- récupération du service d’appartenance

---

## Installation et utilisation en local

### Pré-requis

- Serveur web local : WampServer / Xampp (ou équivalent Apache + PHP + MySQL)
- PHP compatible
- MySQL + phpMyAdmin
- Composer
- Postman (recommandé)

---

### 1) Déploiement des fichiers

1. Télécharger ou cloner le dépôt
2. Copier le dossier du projet dans le répertoire web du serveur local

Exemple Wamp :  
C:\wamp64\www\rest_mediatekdocuments

---

### 2) Installation des dépendances

Dans un terminal, se placer dans le dossier du projet puis exécuter :

composer install

Cela recrée le dossier `vendor/` et installe les dépendances définies dans `composer.json`.

---

### 3) Création de la base de données

1. Ouvrir phpMyAdmin  
2. Créer une base nommée `mediatek86`  
3. Importer le script SQL fourni à la racine du projet :

mediatek86.sql

---

### 4) Configuration du fichier `.env`

Renseigner dans `.env` :

- les informations de connexion MySQL (hôte, base, utilisateur, mot de passe)
- les informations d’authentification API

Les valeurs dépendent de l’environnement local.

---

### 5) Vérification du fonctionnement

Adresse locale :

http://localhost/rest_mediatekdocuments/

Un appel sans route doit renvoyer une erreur HTTP 400 et ne pas afficher la liste des fichiers.

---

## Utilisation de l’API

### Lecture (SELECT)

Méthode HTTP : GET

http://localhost/rest_mediatekdocuments/{table}/{champs}

- `{table}` : nom de table ou ressource
- `{champs}` (optionnel) : filtre JSON (nom/valeur)

---

### Insertion (INSERT)

Méthode HTTP : POST

http://localhost/rest_mediatekdocuments/{table}

Body (x-www-form-urlencoded) :

- clé : `champs`
- valeur : JSON des champs à insérer

---

### Modification (UPDATE)

Méthode HTTP : PUT

http://localhost/rest_mediatekdocuments/{table}/{id}

Body (x-www-form-urlencoded) :

- clé : `champs`
- valeur : JSON des champs à modifier

---

### Suppression (DELETE)

Méthode HTTP : DELETE

http://localhost/rest_mediatekdocuments/{table}/{champs}

- `{champs}` (optionnel) : filtre JSON (nom/valeur)

---

## Tests

### Tests Postman

Pour tester :

- utiliser GET / POST / PUT / DELETE selon la route
- onglet Authorization :
  - Type : Basic Auth
  - renseigner les identifiants configurés dans `.env`

---

## Sécurité

- les informations sensibles (connexion BDD, authentification) sont stockées dans `.env`
- l’accès direct à la racine de l’API est bloqué (HTTP 400)
- les entrées sont contrôlées afin de limiter les erreurs et les usages malveillants

---

## Notes

- cette API est conçue pour répondre aux besoins spécifiques du projet MediaTekDocuments
- pour la structure interne et le fonctionnement générique, se référer au dépôt d’origine 
