# Documentation technique API REST

Generation automatique depuis les sources PHP.

Date de generation: 2026-02-08 23:22:10

## AccessBDD.php

Classes: (aucune declaration de classe)

Methodes:
- `__construct`
- `demande`

## Connexion.php

Classes: Connexion

Methodes:
- `__construct`
- `getInstance`
- `updateBDD`
- `queryBDD`
- `updateBDDTransaction`
- `prepareRequete`

## Controle.php

Classes: Controle

Methodes:
- `__construct`
- `demande`
- `reponse`
- `controleResult`
- `unauthorized`

## index.php

Classes: (aucune declaration de classe)

Methodes:
- (aucune methode detectee)

## MyAccessBDD.php

Classes: MyAccessBDD

Methodes:
- `__construct`
- `traitementSelect`
- `traitementInsert`
- `traitementUpdate`
- `traitementDelete`
- `selectTuplesOneTable`
- `insertOneTupleOneTable`
- `updateOneTupleOneTable`
- `deleteTuplesOneTable`
- `selectTableSimple`
- `selectAllSuivis`
- `selectUtilisateurAuthentifie`
- `selectAllLivres`
- `selectAllDvd`
- `selectAllRevues`
- `selectExemplairesDocument`
- `selectCommandesDocumentByLivreDvd`
- `selectAbonnementsRevue`
- `insertLivre`
- `insertDvd`
- `insertRevue`
- `insertCommandeDocument`
- `insertAbonnement`
- `updateLivre`
- `updateDvd`
- `updateRevue`
- `updateCommandeDocumentSuivi`
- `updateExemplaireEtat`
- `deleteLivre`
- `deleteDvd`
- `deleteRevue`
- `deleteCommandeDocument`
- `deleteAbonnement`
- `deleteExemplaire`
- `extractId`
- `existsInTable`
- `documentHasDependencies`
- `getChamp`
- `getChampInt`
- `getChampFloat`
- `getChampBool`
- `requiredValues`

## Url.php

Classes: Url

Methodes:
- `__construct`
- `getInstance`
- `recupMethodeHTTP`
- `recupVariable`
- `authentification`
- `basicAuthentification`
- `recupAllData`

