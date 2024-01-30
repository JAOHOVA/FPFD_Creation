<?php

/**
 *
 * @version $Id$
 * @author Nevea
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FORMATION
 */
include_once ('FDL/Lib.Dir.php');
include_once ("EXTERNALS/fdl.php");
include_once("FORMATION/class/Lib.Functions.php");


/**
 */

function attestation_france_chimie ( &$action, $mode = 'screen') {

	global $action;

    // Postgres
    $dbaccess = $action->GetParam("FREEDOM_DB");
    //$today = date("d/m/Y", time());
    $today = new DateTimePlus();

    // recuperation id
    if (preg_match("#screen#i", $mode)) {
        $rid = GetHttpVars("id");
        $sous_traitance = false;
        $apprenant_filtered = false;

    }
    else {
        $arrVar = explode("|", $mode);
        $mode = $arrVar[0];
        $rid = $arrVar[1];
        $rand = mt_rand();

        if (count($arrVar) > 3){
            $action_sup = $arrVar[3];
            if ($action_sup == "X2ST"){
                $sous_traitance = false;
                $apprenant_filtered = true;
            }
            elseif($action_sup == "ST") {
                $sous_traitance = true;
                $apprenant_filtered = false;
            }
        }
        else {
            $sous_traitance = false;
            $apprenant_filtered = false;
        }
    }
    

    // Familles concernees
    $famid_af = getFamIdFromName($dbaccess, "MODULE");
    $famid_apprenant = getFamIdFromName($dbaccess, "APPRENANT");
    $famid_apprenantconv = getFamIdFromName($dbaccess, "APPRENANTCONV");

    // quelle famille
    $doc = new_Doc($dbaccess, $rid);
    $famid = $doc->fromid;

    switch ($famid) {
        // module -------------------------------------------------------------
        case $famid_af:
            // --------------------------------------------------------------------
            // liste des apprenants
            $fam_cible = "APPRENANT";
            $oSearch = new SearchDoc($dbaccess, $fam_cible);
            $oSearch->setObjectReturn();
            $oSearch->addFilter("app_modid = '%s'", $rid);
            $oSearch->search();

            $arrAppId = array ();
            while ($apprenant = $oSearch->nextDoc()) {
                $arrAppId[] = $apprenant->getProperty("id");
            }
            break;
			

        // apprenant ----------------------------------------------------------
        case $famid_apprenant:
        case $famid_apprenantconv:
            // --------------------------------------------------------------------
            $arrAppId[] = $rid;
            break;

    }

    //  =======================================================================
    //  Lecture des apprenants
    //  =======================================================================
    $indx = 0;
    $aListeApprenant = array ();

    foreach ($arrAppId as $iIdApprenant) {
        $docApp = new_Doc($dbaccess, $iIdApprenant);

        $aListeApprenant[$indx]['idApprenant'] = $iIdApprenant;
        $aListeApprenant[$indx]['idPersonne'] = $docApp->getRawValue("app_appid");
        $aListeApprenant[$indx]['nom'] = $docApp->getRawValue("app_appnom");
        $aListeApprenant[$indx]['prenom'] = $docApp->getRawValue("app_appprenom");
        $aListeApprenant[$indx]['civilite'] = $docApp->getRawValue("app_appcivilite");
        $aListeApprenant[$indx]['dateNaissance'] = $docApp->getRawValue("app_dtenaissance");
        $aListeApprenant[$indx]['typeFormation'] = $docApp->getRawValue("app_habelecttypeformation");
        $aListeApprenant[$indx]['dateDebut'] = DateConvert::toLocaleString($docApp->getRawValue("app_dateentree"));
        $aListeApprenant[$indx]['dateFin'] = DateConvert::toLocaleString($docApp->getRawValue("app_datesortie"));
        $aListeApprenant[$indx]['idEntreprise'] = $docApp->getRawValue("app_entrepid");
        if(!empty($docApp->getRawValue("app_entrepid"))){
            $oEntreprise = new_Doc("", $docApp->getRawValue("app_entrepid"));
            $aListeApprenant[$indx]['destinataire'] = sprintf("%s\n%s %s\n%s\n%s %s %s", $oEntreprise->getRawValue("si_society"), $docApp->getRawValue("app_appnom"), $docApp->getRawValue("app_appprenom"), preg_replace("#<br>#i", "\n", $oEntreprise->getRawValue("si_addr")), $oEntreprise->getRawValue("si_postcode"), $oEntreprise->getRawValue("si_town"), ($oEntreprise->getRawValue("si_cedex")) ? sprintf("Cedex %s", $oEntreprise->getRawValue("si_cedex")) : "");
        }
        else {
            $aListeApprenant[$indx]['destinataire'] = sprintf("%s %s\n%s\n%s %s", $docApp->getRawValue("app_appnom"), $docApp->getRawValue("app_appprenom"), $docApp->getRawValue("app_appadresse"), $docApp->getRawValue("app_appcpostal"), $docApp->getRawValue("app_appvile"));
        }

        $iIdActionForm = $docApp->getRawValue("app_modid");

        /* Ouverture du module */
        $docModule = new_Doc($dbaccess, $iIdActionForm);
        $id_site = $docModule->getRawValue("md_siteid");

        $aListeApprenant[$indx]["idActionForm"] = $iIdActionForm;
        $aListeApprenant[$indx]["numeroActionForm"] = $docModule->getRawValue("md_nummodule");
        $aListeApprenant[$indx]["libelleActionForm"] = $docModule->getRawValue("md_catalib");

        // Récupère les résultats de l'apprenant
        $aListeApprenant[$indx]["habilitationElect"] = array();
		$docResult = new_Doc($dbaccess, $docApp->getRawValue("app_rltid"));
        $aListeResultat = $docResult->getArrayRawValues("rlt_results");
        foreach($aListeResultat as $aResultatDetail){
			if($aResultatDetail["rlt_pratique"] == "recu" && testCategorieHabilitationElec($aResultatDetail["rlt_catid"])) {
                array_push($aListeApprenant[$indx]["habilitationElect"], $aResultatDetail["rlt_catcode"]);
            }
        }
        // Récupère le testeur
        $aListeIdTesteur = array_unique(explode("\n", $docResult->getRawValue("rlt_testeurid")));
        $aListeTesteur = array_unique(explode("\n", $docResult->getRawValue("rlt_testeur")));

        $indx++;
    }

    // Recherche s'il y a une signature
    $sSignatureTesteur = "";   
    if(sizeof($aListeIdTesteur) == 1){
        $oTesteur = new_Doc($dbaccess, $aListeIdTesteur[0]);
       
        $oDir = new ReadDir($dbaccess);
        $sSignatureLibelleTesteur = $aListeTesteur[0];        
        $sSignatureTesteur = $oDir->FileDir($oTesteur->getRawValue("us_certifsignature"));        
    }

    // Site emetteur
    $docSite = new_Doc($dbaccess, $id_site);

    $arrSite['name'] = $docSite->getRawValue("si_local");
    $arraddr = $docSite->getMultipleRawValues("si_addr");
    if (is_array($arraddr)) {
        foreach ($arraddr as $key => $value) {
            $v = trim($value);
            if (mb_strlen($v) > 0)
                $arrTmp[] = $v;
        }
        $arrSite['addr'] = $arrTmp;
    } else {
        $arrSite['addr'] = $arraddr;
    }
    $arrSite['distrib'] = sprintf("%s %s", $docSite->getRawValue("si_postcode"), $docSite->getRawValue("si_town"));
    $arrSite['tel'] = FormatPhone($docSite->getRawValue("si_phone"));
    $arrSite['web'] = $docSite->getRawValue("si_web");
    $arrSite['mail'] = $docSite->getRawValue("si_mail");
    $arrSite['fax'] = FormatPhone($docSite->getRawValue("si_fax"));
    $arrSite['num'] = $docSite->getRawValue("si_numexist");
    $arrSite["id"] = $docSite->getProperty("id");
    $societe_id = ($docSite->getRawValue("si_idsoc") != '' ? $docSite->getRawValue("si_idsoc") : $docSite->getRawValue("si_id_refsoc"));

    $oSociete = new_Doc($dbaccess, $societe_id);
    

    // CREATION DU PDF --------------------------------------------------------
    $iMargeGauche = 20;
    $iMargeHaute = 12;
    $iMargeDroite = 20;
    $iMarge_horizontale = 2;
	$iMarge_verticale = 5;


    $pdf = new PDFLETTRE();

    $pdf->AcceptPageBreak();

    // Parametres Societe
    $pdf->readDB($dbaccess, $arrSite['id']);

    // Mise en page
    $pdf->SetMargins($iMargeGauche, $iMargeHaute, $iMargeDroite);

    // En tete
    $pdf->site_name = $arrSite['name'];
    $pdf->site_addr = $arrSite['addr'];
    $pdf->site_distrib = $arrSite['distrib'];
    $pdf->site_tel = $arrSite['tel'];
    $pdf->site_fax = $arrSite['fax'];
    $pdf->site_num = $arrSite['num'];

    // Adresse société/personne
    $pdf->lettretitle = "CERTIFICAT";

    $pdf->QRCValue = "";

    $pdf->SetFont('', '', 12);
    $pdf->Open();
    
    $iInterline = 4;

    // CONSTANTE DES HABILITATIONS
    /* $aHabilitationElect = array("operationNonElect" => array(
        "executant" => array("H0 exécutant" => "HO", "H0V exécutant" => "HOV", "B0 exécutant" => "BO", "HF exécutant" => "HF", "BF exécutant" => "BF"),
        "chargeDeChantier" => array("H0 chargé de chantier" => "HO", "H0V chargé de chantier" => "HOV", "B0 chargé de chantier" => "BO", "HF chargé de chantier" => "HF", "BF chargé de chantier" => "BF")
    ),
    "operationElect" => array(
        "executant" => array("H1" => "H1", "H1V" => "H1V", "B1" => "B1", "B1V" => "B1V"),
        "chargeDeTravaux" => array("H2" => "H2", "H2V" => "H2V", "H2V essai" => "H2V essai", "B2" => "B2", "B2V" => "B2V", "B2V essai" => "B2V essai"),
        "chargeInterventionBT" => array("BR" => "BR", "BS" => "BS"),
        "chargeDeConsignation" => array("HC" => "HC", "BC" => "BC"),
        "chargeOperationSpecifique" => array("BE" => "BE", "BE manœuvre" => "BE manoeuvre", "BE vérification" => "BE vérification", "BE mesure" => "BE mesure", "BE essai" => "BE essai", 
                                             "HE" => "HE", "HE vérification" => "HE vérification", "HE manœuvre" => "HE manoeuvre"),
        "habiliteSpecial" => array("BX" => "BX", "BP" => "BP", "BN" => "BN"),
    ));

    $aTableauHabilitation = array("operationNonElect" => array(
        "libelle" => "Travaux d'ordre non électrique et/ou opérations permises", 
        "personnel" => array("executant" => "Exécutant", "chargeDeChantier" => "Chargé de chantier")),
      "operationElect" => array(
        "libelle" => "Opération d'ordre électrique", 
        "personnel" => array("executant" => "Exécutant", "chargeDeTravaux" => "Chargé de travaux", "chargeInterventionBT" => "Chargé d’intervention BT", "chargeDeConsignation" => "Chargé de consignation", "chargeOperationSpecifique" => "Chargé d'opérations spécifiques", "habiliteSpecial" => "Habilité spécial")));
 */
    
    foreach ($aListeApprenant as $aApprenantDetail) {
        // Bloc adresse destinataire
        $pdf->addr_block = $aApprenantDetail['destinataire'];

        $pdf->AddPage();

        // Génération QrCode
        $qrcode = new QRCodeEdit($pdf->lMargin, 50, 15);
        $qrcode->buildQRCode($pdf, "99", $aApprenantDetail["idActionForm"]);
        $qrcode->printLibelle($pdf, "Action de formation : ", $aApprenantDetail["numeroActionForm"]);

        /////////////////////////////////////////////////////////////
        // Partie 1 - texte d'information
        /////////////////////////////////////////////////////////////
        $pdf->SetXY($iMargeGauche, 70);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, $iInterline, sprintf("%s :", "Vous avez participé à une formation intitulée"), 0, "J", 0);
        $pdf->Ln(2);
        $pdf->SetX($iMargeGauche);
        $pdf->SetFont('helvetica', 'B', 10);
        $txt = $aApprenantDetail['libelleActionForm'];
        $pdf->MultiCell(0, $iInterline, $txt, 0, "C", 0);

        $aTexte = array();
        $aTexte[] = sprintf("du %s au %s et %s.", DateConvert::toLocaleString($aApprenantDetail['dateDebut']), DateConvert::toLocaleString($aApprenantDetail['dateFin']), "nous espérons que cette action de formation a répondu à vos attentes");
        $aTexte[] = "Pour justifier de votre participation, outre l'attestation de suivi qui vous a été délivrée, vous trouverez ci-dessous le justificatif que vous devez produire à toute demande officielle.";
        $aTexte[] = "A cet effet, procédez au découpage du document en suivant les pointillés après avoir vérifié l'exactitude des renseignements y figurant.";
        $aTexte[] = "Nous profitons de ce courrier pour vous remercier de votre confiance et vous souhaiter une bonne poursuite professionnelle.";
        $aTexte[] = sprintf("Dans l'attente d'une prochaine rencontre, nous vous prions de croire, %s, à l'expression de nos sentiments dévoués.", $aApprenantDetail['civilite']);

        $pdf->SetFont('helvetica', '', 10);
        foreach ($aTexte as $sTexte) {
            $pdf->Ln(2);
            $pdf->SetX($iMargeGauche);
            $pdf->MultiCell(0, $iInterline, $sTexte, 0, "J", 0);
        }

        // Signature responsable
        $oSignature = new InsertSignature();
        $oSignature->insertSignatureWithText($pdf, 110, $pdf->GetY() - 2, 40, 35, 8.5, 15);

        $pdf->SetDash(0.5, 1);
        $pdf->SetDrawColor(160, 160, 160);
        $pdf->SetLineWidth(0.1);
        $pdf->Line("5", $pdf->A4l / 2 + 6, $pdf->A4w - 5, $pdf->A4l / 2 + 6);
        $pdf->SetDash();


        /////////////////////////////////////////////////////////////
        // Partie 2 - Zone 1 : photo et identité de l'apprenant
        /////////////////////////////////////////////////////////////

        // Définition des positions, tailles, ...
        $xZone1 = $iMargeGauche;
        $yPartie2 = $pdf->A4w / 2 + 86;
        
        //--$aPositionPhoto = array("x" => $xZone1 + 5, "y" => $yPartie2 + 5, "hauteurCadrePhoto" => 33, "largeurCadrePhoto" => 31, "largeurZonePhoto" => 43);
        $aPositionPhoto = array("x" => $xZone1 + 5, "y" => $yPartie2 + 2, "hauteurCadrePhoto" => 0, "largeurCadrePhoto" => 0, "largeurZonePhoto" => 43);

        // Date de la formation
        $pdf->SetX($aPositionPhoto["x"] - 4);
        $pdf->SetFont('helvetica', 'i', 9);
        $pdf->MultiCell($aPositionPhoto["largeurZonePhoto"], $iInterline, "Date de la formation", 0, "L", 0);
        $pdf->Ln(1);    
        $pdf->SetX($aPositionPhoto["x"] - 4);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell($aPositionPhoto["largeurZonePhoto"], $iInterline, "Du ".$aApprenantDetail["dateDebut"]." au ".$aApprenantDetail["dateFin"], 0, "L", 0);
        $pdf->Ln();     

        // Signature testeur
        /* if(!empty($sSignatureLibelleTesteur)){
            $oImg = new ImageGD();
            $oImg->readImage($sSignatureTesteur);
            $oImg->resizeImage("12", "12");

            $pdf->SetX($aPositionPhoto["x"] - 4);
            $pdf->SetFont('helvetica', 'i', 9);
            $pdf->MultiCell($aPositionPhoto["largeurZonePhoto"], $iInterline, "Formateur : ", 0, "L", 0);
            $pdf->ln(1);
            $pdf->SetX($aPositionPhoto["x"] - 4);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->MultiCell($aPositionPhoto["largeurZonePhoto"], $iInterline, $sSignatureLibelleTesteur, 0, "L", 0);
        
            if(!empty($sSignatureTesteur)){                
                $pdf->Image($sSignatureTesteur, $aPositionPhoto["x"] - 4, $pdf->getY(), $oImg->getLargeur(), $oImg->getHauteur());                     
            }  
      
            $pdf->Ln(22);
        } */

        /////////////////////////////////////////////////////////////
        // Partie 2 - Zone 2 : certificat à détacher
        ////////////////////////////////////////////////////////////
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY($xZone1,$yPartie2 - 5);
        $pdf->Cell($iTailleCellule, $iInterline, "<< Certificat à détacher par vos soins et à remettre au titulaire:", 0, "L");
        // Traçage du cadre
        $pdf->Rect($xZone1, $yPartie2, $pdf->A4w - $iMargeGauche - $iMargeDroite, $yPartie2 - 113);
        $iTailleCellule = ($pdf->A4w - $iMargeGauche - $iMargeDroite) / 2 ;
        
        $iHauteurCellule = ($yPartie2 - 113) / 2;
        
        $iDecalage = 5;

        $pdf->Rect($xZone1 + $iTailleCellule, $yPartie2, $iTailleCellule, $iHauteurCellule);
        $pdf->Rect($xZone1 + $iTailleCellule  + ($iTailleCellule / 2), $yPartie2, $iTailleCellule / 2, $iHauteurCellule);
        $pdf->Rect($xZone1 + $iTailleCellule  + ($iTailleCellule / 2), $yPartie2 + $iHauteurCellule, $iTailleCellule / 2, $iHauteurCellule);
        $pdf->Rect($xZone1 + $iTailleCellule , $yPartie2 + $iHauteurCellule, $iTailleCellule, $iHauteurCellule);

        // Alimentation du tableau
        $pdf->SetXY($xZone1, $yPartie2 + $iDecalage - 3);
        $pdf->SetFont('helvetica', 'B', 9);
        // $pdf->MultiCell($iTailleCellule, $iInterline, "FORMATION SÉCURITÉ ENTREPRISES EXTÉRIEURES NIVEAU 1", 0, "L");
        $txt = $aApprenantDetail['libelleActionForm'];
        // Convertir en majuscules avec prise en charge des accents
        $txtEnMajuscules = mb_strtoupper($txt, 'UTF-8');
        $pdf->MultiCell($iTailleCellule, $iInterline, $txtEnMajuscules, 0, "L");
        
        $pdf->SetXY($xZone1, $yPartie2 + $iDecalage + 10);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell($iTailleCellule, $iInterline, "Carte n° :", 0, "L");
        $pdf->SetXY($xZone1 + 24, $yPartie2 + $iDecalage + 10);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->MultiCell($iTailleCellule, $iInterline, "CF23N1-440", 0, "L");

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY($xZone1, $yPartie2 + $iDecalage + 16);
        $pdf->MultiCell($iTailleCellule, $iInterline, "Nom Prénom :", 0, "L");
        $pdf->SetXY($xZone1 + 24, $yPartie2 + $iDecalage + 16);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->MultiCell(20, $iInterline, $aApprenantDetail["nom"]." ".$aApprenantDetail["prenom"], 0, "L");

        // Affichage de la photo
        $x0 = 61;
        $y0 = $pdf->A4l * 2 / 3 ;
        $largeur_zone = $pdf->A4w / 3 - 10;
        $cell_wide = $largeur_zone - 2 * $pdf->iMarge_verticale;
        
        // Cadre photo 25 x 35
        $largeur_cadre = 25;
        $hauteur_cadre = 35;
        
        $x = $x0 + ($largeur_zone - $largeur_cadre) / 2;
        $y = $y0 + $pdf->iMarge_horizontale;
        $pdf->Rect($x, $y, $largeur_cadre, $hauteur_cadre);

        // Ajouter une photo dans le cadre
        $sFichierPhoto = affichePhoto($aApprenantDetail["idPersonne"], $dbaccess);
        if ($sFichierPhoto != '') {
            $pdf->Image($sFichierPhoto, $x, $y, $largeur_cadre, $hauteur_cadre);  
        }

        // Génération QrCode
        $positionY = 234;  // Nouvelle position Y
        $qrcode = new QRCodeEdit($pdf->lMargin + 70, $positionY, 13);
        $qrcode->buildQRCode($pdf, "99", $aApprenantDetail["idActionForm"]);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY($xZone1, $yPartie2 + $iDecalage + 28);
        $pdf->MultiCell($iTailleCellule, $iInterline, "Date de validité :", 0, "L");
        $pdf->SetXY($xZone1 + 24, $yPartie2 + $iDecalage + 28);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->MultiCell(20, $iInterline, $aApprenantDetail["dateDebut"], 0, "L");

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY($xZone1, $yPartie2 + $iDecalage + 33);
        $pdf->MultiCell($iTailleCellule, $iInterline, "Date d'expiration :", 0, "L");
        $pdf->SetXY($xZone1 + 24, $yPartie2 + $iDecalage + 33);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->MultiCell(20, $iInterline, $aApprenantDetail["dateFin"], 0, "L");

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY($xZone1, $yPartie2 + $iDecalage + 40);
        $pdf->MultiCell($iTailleCellule, $iInterline, "Signature du titulaire :", 0, "L");

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetLineWidth(0.5);
        $pdf->Line($xZone1 + 1, $yPartie2 + $iDecalage + 52, $xZone1 + 20, $yPartie2 + $iDecalage + 52);
        $pdf->SetXY($xZone1 + 20, $yPartie2 + $iDecalage + 49);
        $pdf->MultiCell(70, $iInterline, "ORGANISME DE FORMATION", 0, "L");
        $pdf->SetLineWidth(0.5);
        $pdf->Line($xZone1 + $iInterline + 63, $yPartie2 + $iDecalage + 52, $xZone1 + $iInterline + 79, $yPartie2 + $iDecalage + 52);

        // Données Organisme
        $iYZoneOrganisme = $yPartie2 + $iDecalage + 55 + $iInterline;
        // Affiche Logo
        $iLageurLogo = 38;
        // Chemin vers votre image
        $imagePath = $pdf->readParam("LOGOFULL");

        // Définir la largeur souhaitée pour l'image dans le PDF
        $iNouvelleLargeur = $iLageurLogo;

        // Calculer la hauteur proportionnelle en fonction de la nouvelle largeur
        list($iLargeurOriginale, $iHauteurOriginale) = getimagesize($imagePath);
        $iNouvelleHauteur = ($iNouvelleLargeur / $iLargeurOriginale) * $iHauteurOriginale;
        
        // Afficher l'image dans le PDF
        $pdf->Image($imagePath, $xZone1 + 2, $iYZoneOrganisme, $iNouvelleLargeur, $iNouvelleHauteur);

        // Affiche Adresse / mail / web
        $pdf->SetXY($xZone1 +  $iLageurLogo - 11, $iYZoneOrganisme - 5);
        $pdf->SetFont('helvetica', '', 7);
        $sAdresseAvecTirets = '';
        foreach ($arrSite['addr'] as $line) {
            $sAdresseAvecTirets  .= $sAdresseAvecTirets == '' ? "$line" : " - $line";
        }
        $pdf->MultiCell(
            60,
            3,
            "$sAdresseAvecTirets|{$arrSite['distrib']}\nTél:{$arrSite['tel']}\n{$arrSite['mail']}\n{$arrSite['web']}",
            0,
            "C",
            0
        );

        $pdf->SetXY($xZone1 + 86, $yPartie2 + $iDecalage - 2);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(90, $iInterline, "Employeur :", 0, "L");
        $pdf->SetXY($xZone1 + 86, $yPartie2 + $iDecalage + 27);
        $pdf->MultiCell(90, $iInterline, "Date :", 0, "L");

        $pdf->SetXY($xZone1 + 86, $yPartie2 + $iDecalage + 36);
        $pdf->MultiCell(90, $iInterline, "Employeur :", 0, "L");
        $pdf->SetXY($xZone1 + 86, $yPartie2 + $iDecalage + 66);
        $pdf->MultiCell(90, $iInterline, "Date :", 0, "L");

        $pdf->SetXY($xZone1 + 128, $yPartie2 + $iDecalage + 36);
        $pdf->MultiCell(90, $iInterline, "Employeur :", 0, "L");
        $pdf->SetXY($xZone1 + 128, $yPartie2 + $iDecalage + 66);
        $pdf->MultiCell(90, $iInterline, "Date :", 0, "L");

        // Récupère le logo de France Chimie
        $basedoc = new_doc($dbaccess, 'LOGO_FRANCE_CHIMIE');
        $aListeDoc = $basedoc->getMultipleRawValues("BDO_DOCFILE");
        $sFranceChimie = "";
        foreach ($aListeDoc as $sDocument) {
            if (preg_match("#logo_france_chimie.jpg$#", $sDocument)) {
                $sFranceChimie = $sDocument;
                $oDir = new ReadDir($dbaccess);
                $sFranceChimie = $oDir->FileDir($sFranceChimie);
            }
        }

        // Logo France Chimie
        if(!empty($sFranceChimie)){
            // $pdf->SetFillColor(255, 255, 255);
            // $pdf->Rect($xZone1 + 128, $yPartie2 + $iDecalage - 1, 40, 40, "DF");
            $pdf->Image($sFranceChimie , $xZone1 + 128, $yPartie2 + $iDecalage - 3, 41, 36);
        }
        
    }

    // Creer le pdf
    $mode = "file";
    switch ($mode) {
        case "file":
            $filename = sprintf("%s/FDL/tmp/avis_habilitation_electrique_%s.pdf", $action->GetParam("CORE_PUBDIR"), $rand);
            $ret = $pdf->Output($filename, "F");
            return $filename;
            break;

        default:
            $src = $pdf->Output();
            Http_Download($src, 'pdf', 'certificat', true);
            exit();
            break;
    }
}

// Définir la fonction avec le paramètre idPersonne
function affichePhoto($idPersonne, $dbaccess) {
    // Créer une nouvelle instance de document avec idPersonne
    $oPersonne = new_doc("", $idPersonne);
    
    // Obtenir la valeur brute de la photo depuis la base de données
    $sPhoto = $oPersonne->getRawValue("us_photo");

    // Vérifier si la photo existe
    if (!empty($sPhoto)) {
        // Utiliser une classe ReadDir pour obtenir le chemin complet du fichier photo
        $oDir = new ReadDir($dbaccess);
        $sFichierPhoto = $oDir->FileDir($sPhoto);

        // Retourner le chemin complet du fichier photo
        return $sFichierPhoto;
    } else {
        // Retourner une valeur par défaut ou gérer le cas où la photo n'existe pas
        return '';
    }
}

?>