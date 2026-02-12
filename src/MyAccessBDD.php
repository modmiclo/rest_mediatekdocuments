<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){  
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "commandedocument" :
                return $this->selectCommandesDocumentByLivreDvd($champs);
            case "abonnement" :
                return $this->selectAbonnementsRevue($champs);
            case "suivi" :
                return $this->selectAllSuivis();
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "" :
                // return $this->uneFonction(parametres);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->insertLivre($champs);
            case "dvd" :
                return $this->insertDvd($champs);
            case "revue" :
                return $this->insertRevue($champs);
            case "commandedocument" :
                return $this->insertCommandeDocument($champs);
            case "abonnement" :
                return $this->insertAbonnement($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);	
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->updateLivre($id, $champs);
            case "dvd" :
                return $this->updateDvd($id, $champs);
            case "revue" :
                return $this->updateRevue($id, $champs);
            case "commandedocument" :
                return $this->updateCommandeDocumentSuivi($id, $champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }	
    }  
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->deleteLivre($champs);
            case "dvd" :
                return $this->deleteDvd($champs);
            case "revue" :
                return $this->deleteRevue($champs);
            case "commandedocument" :
                return $this->deleteCommandeDocument($champs);
            case "abonnement" :
                return $this->deleteAbonnement($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	          
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array{
        $requete = "select * from $table order by libelle;";		
        return $this->conn->queryBDD($requete);	    
    }

    /**
     * Récupère les étapes de suivi ordonnées.
     * @return array|null
     */
    private function selectAllSuivis() : ?array{
        $requete = "select id, libelle, ordre from suivi order by ordre;";
        return $this->conn->queryBDD($requete);
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array{
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";		
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array{
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";	
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array{
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs 
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }

    /**
     * Récupère les commandes d'un livre/dvd, triées par date décroissante.
     * @param array|null $champs
     * @return array|null
     */
    private function selectCommandesDocumentByLivreDvd(?array $champs) : ?array{
        if (empty($champs)) {
            return null;
        }
        $idLivreDvd = $this->getChamp($champs, ['idLivreDvd', 'idlivredvd', 'id']);
        if (is_null($idLivreDvd) || !$this->existsInTable('livres_dvd', $idLivreDvd)) {
            return null;
        }
        $requete = "select c.id, c.dateCommande, c.montant, cd.nbExemplaire, cd.idLivreDvd, cd.idSuivi, s.libelle as etapeSuivi, s.ordre as ordreSuivi ";
        $requete .= "from commande c ";
        $requete .= "join commandedocument cd on cd.id = c.id ";
        $requete .= "join suivi s on s.id = cd.idSuivi ";
        $requete .= "where cd.idLivreDvd = :idLivreDvd ";
        $requete .= "order by c.dateCommande desc, c.id desc;";
        return $this->conn->queryBDD($requete, ['idLivreDvd' => $idLivreDvd]);
    }

    /**
     * Récupère les abonnements de revue triés par date de commande décroissante.
     * Si champs['finProche']=true, retourne les revues dont l'abonnement finit dans moins de 30 jours.
     * @param array|null $champs
     * @return array|null
     */
    private function selectAbonnementsRevue(?array $champs) : ?array{
        $finProche = $this->getChampBool($champs, ['finProche', 'finproche']);
        if ($finProche) {
            $requete = "select d.titre as titreRevue, a.idRevue, a.dateFinAbonnement ";
            $requete .= "from abonnement a ";
            $requete .= "join document d on d.id = a.idRevue ";
            $requete .= "where a.dateFinAbonnement >= CURDATE() and a.dateFinAbonnement <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ";
            $requete .= "order by a.dateFinAbonnement asc, d.titre asc;";
            return $this->conn->queryBDD($requete);
        }

        if (empty($champs)) {
            return $this->conn->queryBDD("select id, dateFinAbonnement, idRevue from abonnement order by id desc;");
        }
        $idRevue = $this->getChamp($champs, ['idRevue', 'idrevue', 'id']);
        if (is_null($idRevue) || !$this->existsInTable('revue', $idRevue)) {
            return null;
        }
        $requete = "select c.id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue ";
        $requete .= "from abonnement a ";
        $requete .= "join commande c on c.id = a.id ";
        $requete .= "where a.idRevue = :idRevue ";
        $requete .= "order by c.dateCommande desc, c.id desc;";
        return $this->conn->queryBDD($requete, ['idRevue' => $idRevue]);
    }

    /**
     * Insertion transactionnelle d'un livre (document + livres_dvd + livre).
     * @param array|null $champs
     * @return int|null
     */
    private function insertLivre(?array $champs) : ?int{
        if (empty($champs)) {
            return null;
        }
        $id = $this->getChamp($champs, ['id', 'Id']);
        $titre = $this->getChamp($champs, ['titre', 'Titre']);
        $image = $this->getChamp($champs, ['image', 'Image']);
        $idRayon = $this->getChamp($champs, ['idRayon', 'idrayon', 'IdRayon']);
        $idPublic = $this->getChamp($champs, ['idPublic', 'idpublic', 'IdPublic']);
        $idGenre = $this->getChamp($champs, ['idGenre', 'idgenre', 'IdGenre']);
        $isbn = $this->getChamp($champs, ['isbn', 'ISBN', 'Isbn'], true);
        $auteur = $this->getChamp($champs, ['auteur', 'Auteur'], true);
        $collection = $this->getChamp($champs, ['collection', 'Collection'], true);
        if (!$this->requiredValues([$id, $titre, $idRayon, $idPublic, $idGenre])) {
            return null;
        }
        $paramsDocument = [
            'id' => $id,
            'titre' => $titre,
            'image' => $image,
            'idRayon' => $idRayon,
            'idPublic' => $idPublic,
            'idGenre' => $idGenre
        ];
        $paramsSpecific = [
            'id' => $id,
            'isbn' => $isbn,
            'auteur' => $auteur,
            'collection' => $collection
        ];
        $operations = [
            ['sql' => "insert into document (id, titre, image, idRayon, idPublic, idGenre) values (:id, :titre, :image, :idRayon, :idPublic, :idGenre)", 'params' => $paramsDocument, 'mustAffect' => true],
            ['sql' => "insert into livres_dvd (id) values (:id)", 'params' => ['id' => $id], 'mustAffect' => true],
            ['sql' => "insert into livre (id, ISBN, auteur, collection) values (:id, :isbn, :auteur, :collection)", 'params' => $paramsSpecific, 'mustAffect' => true]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Insertion transactionnelle d'un dvd (document + livres_dvd + dvd).
     * @param array|null $champs
     * @return int|null
     */
    private function insertDvd(?array $champs) : ?int{
        if (empty($champs)) {
            return null;
        }
        $id = $this->getChamp($champs, ['id', 'Id']);
        $titre = $this->getChamp($champs, ['titre', 'Titre']);
        $image = $this->getChamp($champs, ['image', 'Image']);
        $idRayon = $this->getChamp($champs, ['idRayon', 'idrayon', 'IdRayon']);
        $idPublic = $this->getChamp($champs, ['idPublic', 'idpublic', 'IdPublic']);
        $idGenre = $this->getChamp($champs, ['idGenre', 'idgenre', 'IdGenre']);
        $synopsis = $this->getChamp($champs, ['synopsis', 'Synopsis'], true);
        $realisateur = $this->getChamp($champs, ['realisateur', 'Realisateur'], true);
        $duree = $this->getChampInt($champs, ['duree', 'Duree']);
        if (!$this->requiredValues([$id, $titre, $idRayon, $idPublic, $idGenre]) || is_null($duree)) {
            return null;
        }
        $paramsDocument = [
            'id' => $id,
            'titre' => $titre,
            'image' => $image,
            'idRayon' => $idRayon,
            'idPublic' => $idPublic,
            'idGenre' => $idGenre
        ];
        $paramsSpecific = [
            'id' => $id,
            'synopsis' => $synopsis,
            'realisateur' => $realisateur,
            'duree' => $duree
        ];
        $operations = [
            ['sql' => "insert into document (id, titre, image, idRayon, idPublic, idGenre) values (:id, :titre, :image, :idRayon, :idPublic, :idGenre)", 'params' => $paramsDocument, 'mustAffect' => true],
            ['sql' => "insert into livres_dvd (id) values (:id)", 'params' => ['id' => $id], 'mustAffect' => true],
            ['sql' => "insert into dvd (id, synopsis, realisateur, duree) values (:id, :synopsis, :realisateur, :duree)", 'params' => $paramsSpecific, 'mustAffect' => true]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Insertion transactionnelle d'une revue (document + revue).
     * @param array|null $champs
     * @return int|null
     */
    private function insertRevue(?array $champs) : ?int{
        if (empty($champs)) {
            return null;
        }
        $id = $this->getChamp($champs, ['id', 'Id']);
        $titre = $this->getChamp($champs, ['titre', 'Titre']);
        $image = $this->getChamp($champs, ['image', 'Image']);
        $idRayon = $this->getChamp($champs, ['idRayon', 'idrayon', 'IdRayon']);
        $idPublic = $this->getChamp($champs, ['idPublic', 'idpublic', 'IdPublic']);
        $idGenre = $this->getChamp($champs, ['idGenre', 'idgenre', 'IdGenre']);
        $periodicite = $this->getChamp($champs, ['periodicite', 'Periodicite']);
        $delai = $this->getChampInt($champs, ['delaiMiseADispo', 'delaimiseadispo', 'DelaiMiseADispo']);
        if (!$this->requiredValues([$id, $titre, $idRayon, $idPublic, $idGenre, $periodicite]) || is_null($delai)) {
            return null;
        }
        $paramsDocument = [
            'id' => $id,
            'titre' => $titre,
            'image' => $image,
            'idRayon' => $idRayon,
            'idPublic' => $idPublic,
            'idGenre' => $idGenre
        ];
        $paramsSpecific = [
            'id' => $id,
            'periodicite' => $periodicite,
            'delaiMiseADispo' => $delai
        ];
        $operations = [
            ['sql' => "insert into document (id, titre, image, idRayon, idPublic, idGenre) values (:id, :titre, :image, :idRayon, :idPublic, :idGenre)", 'params' => $paramsDocument, 'mustAffect' => true],
            ['sql' => "insert into revue (id, periodicite, delaiMiseADispo) values (:id, :periodicite, :delaiMiseADispo)", 'params' => $paramsSpecific, 'mustAffect' => true]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Insertion transactionnelle d'une commande de livre/dvd (commande + commandedocument).
     * @param array|null $champs
     * @return int|null
     */
    private function insertCommandeDocument(?array $champs) : ?int{
        if (empty($champs)) {
            return null;
        }
        $id = $this->getChamp($champs, ['id', 'Id']);
        $dateCommande = $this->getChamp($champs, ['dateCommande', 'datecommande', 'DateCommande']);
        $montant = $this->getChampFloat($champs, ['montant', 'Montant']);
        $nbExemplaire = $this->getChampInt($champs, ['nbExemplaire', 'nbexemplaire', 'NbExemplaire']);
        $idLivreDvd = $this->getChamp($champs, ['idLivreDvd', 'idlivredvd', 'IdLivreDvd']);
        $idSuivi = $this->getChamp($champs, ['idSuivi', 'idsuivi', 'IdSuivi'], true);
        if ($idSuivi === '') {
            $idSuivi = '00001';
        }
        if (!$this->requiredValues([$id, $dateCommande, $idLivreDvd, $idSuivi]) || is_null($montant) || is_null($nbExemplaire) || $nbExemplaire <= 0 || $montant < 0) {
            return null;
        }
        if ($this->existsInTable('commande', $id) || !$this->existsInTable('livres_dvd', $idLivreDvd) || !$this->existsInTable('suivi', $idSuivi)) {
            return null;
        }
        $operations = [
            ['sql' => "insert into commande (id, dateCommande, montant) values (:id, :dateCommande, :montant)", 'params' => ['id' => $id, 'dateCommande' => $dateCommande, 'montant' => $montant], 'mustAffect' => true],
            ['sql' => "insert into commandedocument (id, nbExemplaire, idLivreDvd, idSuivi) values (:id, :nbExemplaire, :idLivreDvd, :idSuivi)", 'params' => ['id' => $id, 'nbExemplaire' => $nbExemplaire, 'idLivreDvd' => $idLivreDvd, 'idSuivi' => $idSuivi], 'mustAffect' => true]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Insertion transactionnelle d'un abonnement (commande + abonnement).
     * @param array|null $champs
     * @return int|null
     */
    private function insertAbonnement(?array $champs) : ?int{
        if (empty($champs)) {
            return null;
        }
        $id = $this->getChamp($champs, ['id', 'Id']);
        $dateCommande = $this->getChamp($champs, ['dateCommande', 'datecommande', 'DateCommande']);
        $montant = $this->getChampFloat($champs, ['montant', 'Montant']);
        $dateFinAbonnement = $this->getChamp($champs, ['dateFinAbonnement', 'datefinabonnement', 'DateFinAbonnement']);
        $idRevue = $this->getChamp($champs, ['idRevue', 'idrevue', 'IdRevue']);

        if (!$this->requiredValues([$id, $dateCommande, $dateFinAbonnement, $idRevue]) || is_null($montant) || $montant < 0) {
            return null;
        }
        if (strtotime($dateFinAbonnement) < strtotime($dateCommande)) {
            return null;
        }
        if ($this->existsInTable('commande', $id) || !$this->existsInTable('revue', $idRevue)) {
            return null;
        }

        $operations = [
            ['sql' => "insert into commande (id, dateCommande, montant) values (:id, :dateCommande, :montant)", 'params' => ['id' => $id, 'dateCommande' => $dateCommande, 'montant' => $montant], 'mustAffect' => true],
            ['sql' => "insert into abonnement (id, dateFinAbonnement, idRevue) values (:id, :dateFinAbonnement, :idRevue)", 'params' => ['id' => $id, 'dateFinAbonnement' => $dateFinAbonnement, 'idRevue' => $idRevue], 'mustAffect' => true]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Mise à jour transactionnelle d'un livre.
     * @param string|null $id
     * @param array|null $champs
     * @return int|null
     */
    private function updateLivre(?string $id, ?array $champs) : ?int{
        if (is_null($id) || empty($champs) || !$this->existsInTable('livre', $id)) {
            return null;
        }
        $titre = $this->getChamp($champs, ['titre', 'Titre']);
        $image = $this->getChamp($champs, ['image', 'Image']);
        $idRayon = $this->getChamp($champs, ['idRayon', 'idrayon', 'IdRayon']);
        $idPublic = $this->getChamp($champs, ['idPublic', 'idpublic', 'IdPublic']);
        $idGenre = $this->getChamp($champs, ['idGenre', 'idgenre', 'IdGenre']);
        $isbn = $this->getChamp($champs, ['isbn', 'ISBN', 'Isbn'], true);
        $auteur = $this->getChamp($champs, ['auteur', 'Auteur'], true);
        $collection = $this->getChamp($champs, ['collection', 'Collection'], true);
        if (!$this->requiredValues([$titre, $idRayon, $idPublic, $idGenre])) {
            return null;
        }
        $operations = [
            ['sql' => "update document set titre=:titre, image=:image, idRayon=:idRayon, idPublic=:idPublic, idGenre=:idGenre where id=:id", 'params' => ['id' => $id, 'titre' => $titre, 'image' => $image, 'idRayon' => $idRayon, 'idPublic' => $idPublic, 'idGenre' => $idGenre], 'mustAffect' => false],
            ['sql' => "update livre set ISBN=:isbn, auteur=:auteur, collection=:collection where id=:id", 'params' => ['id' => $id, 'isbn' => $isbn, 'auteur' => $auteur, 'collection' => $collection], 'mustAffect' => false]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Mise à jour transactionnelle d'un dvd.
     * @param string|null $id
     * @param array|null $champs
     * @return int|null
     */
    private function updateDvd(?string $id, ?array $champs) : ?int{
        if (is_null($id) || empty($champs) || !$this->existsInTable('dvd', $id)) {
            return null;
        }
        $titre = $this->getChamp($champs, ['titre', 'Titre']);
        $image = $this->getChamp($champs, ['image', 'Image']);
        $idRayon = $this->getChamp($champs, ['idRayon', 'idrayon', 'IdRayon']);
        $idPublic = $this->getChamp($champs, ['idPublic', 'idpublic', 'IdPublic']);
        $idGenre = $this->getChamp($champs, ['idGenre', 'idgenre', 'IdGenre']);
        $synopsis = $this->getChamp($champs, ['synopsis', 'Synopsis'], true);
        $realisateur = $this->getChamp($champs, ['realisateur', 'Realisateur'], true);
        $duree = $this->getChampInt($champs, ['duree', 'Duree']);
        if (!$this->requiredValues([$titre, $idRayon, $idPublic, $idGenre]) || is_null($duree)) {
            return null;
        }
        $operations = [
            ['sql' => "update document set titre=:titre, image=:image, idRayon=:idRayon, idPublic=:idPublic, idGenre=:idGenre where id=:id", 'params' => ['id' => $id, 'titre' => $titre, 'image' => $image, 'idRayon' => $idRayon, 'idPublic' => $idPublic, 'idGenre' => $idGenre], 'mustAffect' => false],
            ['sql' => "update dvd set synopsis=:synopsis, realisateur=:realisateur, duree=:duree where id=:id", 'params' => ['id' => $id, 'synopsis' => $synopsis, 'realisateur' => $realisateur, 'duree' => $duree], 'mustAffect' => false]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Mise à jour transactionnelle d'une revue.
     * @param string|null $id
     * @param array|null $champs
     * @return int|null
     */
    private function updateRevue(?string $id, ?array $champs) : ?int{
        if (is_null($id) || empty($champs) || !$this->existsInTable('revue', $id)) {
            return null;
        }
        $titre = $this->getChamp($champs, ['titre', 'Titre']);
        $image = $this->getChamp($champs, ['image', 'Image']);
        $idRayon = $this->getChamp($champs, ['idRayon', 'idrayon', 'IdRayon']);
        $idPublic = $this->getChamp($champs, ['idPublic', 'idpublic', 'IdPublic']);
        $idGenre = $this->getChamp($champs, ['idGenre', 'idgenre', 'IdGenre']);
        $periodicite = $this->getChamp($champs, ['periodicite', 'Periodicite']);
        $delai = $this->getChampInt($champs, ['delaiMiseADispo', 'delaimiseadispo', 'DelaiMiseADispo']);
        if (!$this->requiredValues([$titre, $idRayon, $idPublic, $idGenre, $periodicite]) || is_null($delai)) {
            return null;
        }
        $operations = [
            ['sql' => "update document set titre=:titre, image=:image, idRayon=:idRayon, idPublic=:idPublic, idGenre=:idGenre where id=:id", 'params' => ['id' => $id, 'titre' => $titre, 'image' => $image, 'idRayon' => $idRayon, 'idPublic' => $idPublic, 'idGenre' => $idGenre], 'mustAffect' => false],
            ['sql' => "update revue set periodicite=:periodicite, delaiMiseADispo=:delaiMiseADispo where id=:id", 'params' => ['id' => $id, 'periodicite' => $periodicite, 'delaiMiseADispo' => $delai], 'mustAffect' => false]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Met à jour l'étape de suivi d'une commande de document.
     * @param string|null $id
     * @param array|null $champs
     * @return int|null
     */
    private function updateCommandeDocumentSuivi(?string $id, ?array $champs) : ?int{
        if (is_null($id) || empty($champs) || !$this->existsInTable('commandedocument', $id)) {
            return null;
        }
        $idSuivi = $this->getChamp($champs, ['idSuivi', 'idsuivi', 'IdSuivi']);
        if (is_null($idSuivi) || !$this->existsInTable('suivi', $idSuivi)) {
            return null;
        }
        return $this->conn->updateBDD("update commandedocument set idSuivi=:idSuivi where id=:id", ['id' => $id, 'idSuivi' => $idSuivi]);
    }

    /**
     * Suppression transactionnelle d'un livre avec contrôle des dépendances.
     * @param array|null $champs
     * @return int|null
     */
    private function deleteLivre(?array $champs) : ?int{
        $id = $this->extractId($champs);
        if (is_null($id) || !$this->existsInTable('livre', $id) || $this->documentHasDependencies($id, true)) {
            return null;
        }
        $operations = [
            ['sql' => "delete from livre where id=:id", 'params' => ['id' => $id], 'mustAffect' => true],
            ['sql' => "delete from livres_dvd where id=:id", 'params' => ['id' => $id], 'mustAffect' => true],
            ['sql' => "delete from document where id=:id", 'params' => ['id' => $id], 'mustAffect' => true]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Suppression transactionnelle d'un dvd avec contrôle des dépendances.
     * @param array|null $champs
     * @return int|null
     */
    private function deleteDvd(?array $champs) : ?int{
        $id = $this->extractId($champs);
        if (is_null($id) || !$this->existsInTable('dvd', $id) || $this->documentHasDependencies($id, true)) {
            return null;
        }
        $operations = [
            ['sql' => "delete from dvd where id=:id", 'params' => ['id' => $id], 'mustAffect' => true],
            ['sql' => "delete from livres_dvd where id=:id", 'params' => ['id' => $id], 'mustAffect' => true],
            ['sql' => "delete from document where id=:id", 'params' => ['id' => $id], 'mustAffect' => true]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Suppression transactionnelle d'une revue avec contrôle des dépendances.
     * @param array|null $champs
     * @return int|null
     */
    private function deleteRevue(?array $champs) : ?int{
        $id = $this->extractId($champs);
        if (is_null($id) || !$this->existsInTable('revue', $id) || $this->documentHasDependencies($id, false)) {
            return null;
        }
        $operations = [
            ['sql' => "delete from revue where id=:id", 'params' => ['id' => $id], 'mustAffect' => true],
            ['sql' => "delete from document where id=:id", 'params' => ['id' => $id], 'mustAffect' => true]
        ];
        return $this->conn->updateBDDTransaction($operations);
    }

    /**
     * Supprime une commande de document via la table mère commande.
     * Le trigger gère la suppression de commandedocument et les contrôles métier.
     * @param array|null $champs
     * @return int|null
     */
    private function deleteCommandeDocument(?array $champs) : ?int{
        $id = $this->extractId($champs);
        if (is_null($id) || !$this->existsInTable('commandedocument', $id)) {
            return null;
        }
        return $this->conn->updateBDD("delete from commande where id=:id", ['id' => $id]);
    }

    /**
     * Supprime un abonnement si aucun exemplaire n'est dans sa période.
     * @param array|null $champs
     * @return int|null
     */
    private function deleteAbonnement(?array $champs) : ?int{
        $id = $this->extractId($champs);
        if (is_null($id) || !$this->existsInTable('abonnement', $id)) {
            return null;
        }
        $requete = "select a.idRevue, c.dateCommande, a.dateFinAbonnement ";
        $requete .= "from abonnement a join commande c on c.id = a.id where a.id = :id";
        $rows = $this->conn->queryBDD($requete, ['id' => $id]);
        if (is_null($rows) || empty($rows)) {
            return null;
        }
        $abonnement = $rows[0];
        $count = $this->conn->queryBDD(
            "select count(*) as nb from exemplaire where id = :idRevue and dateAchat between :dateCommande and :dateFinAbonnement",
            [
                'idRevue' => $abonnement['idRevue'],
                'dateCommande' => $abonnement['dateCommande'],
                'dateFinAbonnement' => $abonnement['dateFinAbonnement']
            ]
        );
        $nb = (int)($count[0]['nb'] ?? 0);
        if ($nb > 0) {
            return null;
        }
        return $this->conn->updateBDD("delete from commande where id=:id", ['id' => $id]);
    }

    /**
     * Récupère l'identifiant à partir d'un tableau de champs.
     * @param array|null $champs
     * @return string|null
     */
    private function extractId(?array $champs) : ?string{
        if (empty($champs)) {
            return null;
        }
        return $this->getChamp($champs, ['id', 'Id']);
    }

    /**
     * Contrôle l'existence d'un id dans une table donnée.
     * @param string $table
     * @param string $id
     * @return bool
     */
    private function existsInTable(string $table, string $id) : bool{
        $result = $this->conn->queryBDD("select count(*) as nb from $table where id = :id", ['id' => $id]);
        if (is_null($result) || empty($result)) {
            return false;
        }
        return ((int)($result[0]['nb'] ?? 0)) > 0;
    }

    /**
     * Contrôle les dépendances interdisant la suppression d'un document.
     * @param string $id
     * @param bool $checkCommandeLivreDvd true pour contrôler commandedocument
     * @return bool true si le document ne peut pas être supprimé
     */
    private function documentHasDependencies(string $id, bool $checkCommandeLivreDvd) : bool{
        $exemplaires = $this->conn->queryBDD("select count(*) as nb from exemplaire where id = :id", ['id' => $id]);
        $nbExemplaires = (int)($exemplaires[0]['nb'] ?? 0);
        if ($nbExemplaires > 0) {
            return true;
        }
        if ($checkCommandeLivreDvd) {
            $commandes = $this->conn->queryBDD("select count(*) as nb from commandedocument where idLivreDvd = :id", ['id' => $id]);
            $nbCommandes = (int)($commandes[0]['nb'] ?? 0);
            return $nbCommandes > 0;
        }
        $abonnements = $this->conn->queryBDD("select count(*) as nb from abonnement where idRevue = :id", ['id' => $id]);
        $nbAbonnements = (int)($abonnements[0]['nb'] ?? 0);
        return $nbAbonnements > 0;
    }

    /**
     * Retourne la première valeur trouvée parmi plusieurs noms de champ.
     * @param array $source
     * @param array $noms
     * @param bool $nullable
     * @return string|null
     */
    private function getChamp(array $source, array $noms, bool $nullable=false) : ?string{
        foreach ($noms as $nom){
            if (array_key_exists($nom, $source)){
                $value = trim((string)$source[$nom]);
                if ($value === '' && !$nullable){
                    return null;
                }
                return $value;
            }
        }
        return $nullable ? '' : null;
    }

    /**
     * Retourne un entier à partir d'un ensemble de noms possibles.
     * @param array $source
     * @param array $noms
     * @return int|null
     */
    private function getChampInt(array $source, array $noms) : ?int{
        $value = $this->getChamp($source, $noms);
        if (is_null($value) || !is_numeric($value)) {
            return null;
        }
        return (int)$value;
    }

    /**
     * Retourne un décimal à partir d'un ensemble de noms possibles.
     * @param array $source
     * @param array $noms
     * @return float|null
     */
    private function getChampFloat(array $source, array $noms) : ?float{
        $value = $this->getChamp($source, $noms);
        if (is_null($value) || !is_numeric($value)) {
            return null;
        }
        return (float)$value;
    }

    /**
     * Retourne un booléen à partir d'un ensemble de noms possibles.
     * @param array|null $source
     * @param array $noms
     * @return bool
     */
    private function getChampBool(?array $source, array $noms) : bool{
        if (empty($source)) {
            return false;
        }
        foreach ($noms as $nom) {
            if (array_key_exists($nom, $source)) {
                $value = strtolower(trim((string)$source[$nom]));
                return ($value === '1' || $value === 'true' || $value === 'yes' || $value === 'oui');
            }
        }
        return false;
    }

    /**
     * Vérifie que toutes les valeurs requises sont présentes.
     * @param array $values
     * @return bool
     */
    private function requiredValues(array $values) : bool{
        foreach ($values as $value){
            if (is_null($value) || trim((string)$value) === ''){
                return false;
            }
        }
        return true;
    }		    
    
}
