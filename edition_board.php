<?php
/**
 *
 * @version $Id$
 * @author Nevea
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FORMATION
 */
/**
 * 
 */
include_once ("FDL/Lib.Attr.php");
//include_once ("FDL/Class.DocFam.php");
//include_once ("FDL/Class.Doc.php");
include_once ("EXTERNALS/fdl.php");
include_once ("FDL/Lib.Dir.php");
include_once ("FDL/freedom_util.php");
include_once ("FDL/modcard.php");
//include_once ("FDL/Class.Dir.php");
include_once ("FDL/editutil.php");
//include_once ("FDL/Class.StringBuilder.php");
/**
 * 
 */

function edition_board ( $action ) {

	// Récupérer l'id de l'af
	$modid = GetHttpVars("modid");
	
	// Passer en mode édition
	editmode($action);
	
	// Connexion DB
	$dbaccess = $action->GetParam("FREEDOM_DB");
	
	// Action de formation
	$oAF = new_Doc($dbaccess, $modid);
	
	// Dates de l'action de formation
	$datedebut = DateConvert::toLocaleString($oAF->getRawValue("md_dtedebut"), 's');
	$datefin = DateConvert::toLocaleString($oAF->getRawValue("md_dtefin"), 's');

    // Identification fiche catalogue
    $catid = $oAF->getRawValue("md_cataid");
    $docCat = new_Doc($dbaccess, $catid);

    // Ajout des spécificités clients
    $sEtiquetteApprenant = "";
    if($action->getParam("CORE_CLIENT") == "Arep Fresc") {
        $sEtiquetteApprenant = '<tr>';
        $sEtiquetteApprenant .= '<td width="5%">';
        $sEtiquetteApprenant .= '<div align="center">';
        $sEtiquetteApprenant .= '<input type="checkbox" name="chkEtiquette" id="chkEtiquette" />';
        $sEtiquetteApprenant .= '</div>';
        $sEtiquetteApprenant .= '</td>';
        $sEtiquetteApprenant .= '<td width="40%">Etiquette apprenant</td>';
        $sEtiquetteApprenant .= '<td width="55%">&nbsp;</td>';
        $sEtiquetteApprenant .= '</tr>';
    }

    $action->lay->Set("ETIQUETTE_APPRENANT", $sEtiquetteApprenant);

    // Passage sur le layout
	$action->lay->Set("MODLIB", sprintf("%s du %s au %s", $oAF->getTitle(), $datedebut, $datefin));
	$action->lay->Set("MODID", $modid);

    $begin = new DateTimePlus($datedebut);
    $end = new DateTimePlus($datefin);
    $end = $end->modify('+ 1 day');

    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($begin, $interval, $end);

    $arrSemaine[0] = "Toutes les semaines";
    $arrMois[0] = "Toutes les mois";
    foreach ($daterange as $day){
        $key = $day->getMonday();
        if (!isset($arrSemaine[$key->toShortString()])){
            $arrSemaine[$key->toShortString()] = buildTxtSemaine($day);
        }

        $keyMois = $day->getMonth()."/".$day->getYear();
        if (!isset($arrMois[$keyMois])){
            $arrMois[$keyMois] = buildTxtMois($keyMois, $day);
        }
    }

    // Construction du select pour les semaines
	$html = array ();
	$html[] = "<select id='keysemaine' name='_keysemaine'>";
	foreach ($arrSemaine as $key => $value) {
		$html[] = sprintf("<option value='%s'%s>%s</option>", $key, (($key == "0") ? " selected" : ""), $value);
	}
	$html[] = "</select>";
    $select = implode("\n", $html);
	$action->lay->Set("SELECTWEEK", $select);

    // Construction du select pour les mois
    $html = array ();
    $html[] = "<select id='keymois' name='_keymois'>";
    foreach ($arrMois as $key => $value) {
        $html[] = sprintf("<option value='%s'%s>%s</option>", $key, (($key == "0") ? " selected" : ""), $value);
    }
    $html[] = "</select>";
    $select = implode("\n", $html);
    $action->lay->Set("SELECTMONTH", $select);

	
	// traitement du dossier complémentaire BTNDOSSIER / CAT_LISTEPREPASID
	// [_blank]%S%app=FPDF&action=SELECT_LISTEPIECES&modid=%I%
	$catid = $oAF->getRawValue("md_cataid");
   	$oCatalogue = new_doc($dbaccess, $catid);
	$liste_prepasid = $oCatalogue->getRawValue("cat_listeprepasid");
	
	$bouton = '';
	if (mb_strlen($liste_prepasid) > 0){
		$bouton = sprintf("<input id='btnprepa' type='button' value='Documents complémentaires...' onclick=\"openPrepa('%s')\"/>", $modid);			
	} else {
		$bouton = "<span id='btnprepa'>&nbsp;</span>";
	}
	
	// B96
	$code = $oAF->getRawValue("md_catalib");
	if (preg_match("/b96/i", $code)){
		$tr_b96 = buildTR("odd", "AttestationB96", "Attestation de suivi de formation B96");
        $classe_select = "";

	} else {
		$tr_b96 = '';
        $classe_select = "class='odd'";

	}

	// AIPR Afficher l'action ou non
    $aTypeFormation = $docCat->getRawValue("cat_typeformation");
    $aTypeFormation = $docCat->rawValueToArray($aTypeFormation);
    (in_array('AIPR', $aTypeFormation)) ? $ScreenAIPR =  'yes' : $ScreenAIPR =  'none' ;

    // APASR ASS Afficher l'action ou non
    $aTypeFormation = $docCat->getRawValue("cat_typeformation");
    $aTypeFormation = $docCat->rawValueToArray($aTypeFormation);
    (in_array('ATTEST_RECUPERATION_POINT', $aTypeFormation)) ? $ScreenAttestationRecuperationPoint =  'yes' : $ScreenAttestationRecuperationPoint =  'none' ;

    // Autorisation conduite Afficher l'action ou non
    $aTypeFormation = $docCat->getRawValue("cat_typeformation");
    $aTypeFormation = $docCat->rawValueToArray($aTypeFormation);
    (in_array('AUTORISATION_CONDUITE', $aTypeFormation)) ? $ScreenAutorisationConduite =  'yes' : $ScreenAutorisationConduite =  'none' ;

    // Autorisation conduite formation Afficher l'action ou non
    $aTypeFormation = $docCat->getRawValue("cat_typeformation");
    $aTypeFormation = $docCat->rawValueToArray($aTypeFormation);
    (in_array('AUTORISATION_CONDUITE_FORMATION', $aTypeFormation)) ? $ScreenAutorisationConduiteFormation =  'yes' : $ScreenAutorisationConduiteFormation =  'none' ;

    // Autorisation Permis de conduire Pratique Afficher l'action ou non
    $aTypeFormation = $docCat->getRawValue("cat_typeformation");
    $aTypeFormation = $docCat->rawValueToArray($aTypeFormation);
    (in_array('PERMIS', $aTypeFormation)) ? $ScreenPermisConduirePratique =  'yes' : $ScreenPermisConduirePratique =  'none' ;

    // Attestation France Chimie Afficher l'action ou non
    $aTypeFormation = $docCat->getRawValue("cat_typeformation");
    $aTypeFormation = $docCat->rawValueToArray($aTypeFormation);
    (in_array('FRANCE_CHIMIE', $aTypeFormation)) ? $ScreenFranceChimie =  'yes' : $ScreenFranceChimie =  'none' ;


    // Mobilité Afficher l'action ou non
    $aTypeFormation = $docCat->getRawValue("cat_typeformation");
    $aTypeFormation = $docCat->rawValueToArray($aTypeFormation);
    (in_array('ATTEST_SUIVI_FORM_MOBILITE', $aTypeFormation)) ? $ScreenAttestationSuiviFormationMoibilite =  'yes' : $ScreenAttestationSuiviFormationMoibilite =  'none' ;

    // Gestion de la liste des apprenants
    $aApprenant = retourneListeApprenantActionFormation($modid);
    if(sizeof($aApprenant) > 0) {
        $sListeApprenant = '<div class="form-group has-info" id="groupe_listeAgence">';
        $sListeApprenant .= '<div class="dropdown bootstrap-select show-tick form-control form-control-sm has-info">';
        $sListeApprenant .= '<select id="id_listeApprenant" name="listeApprenant" multiple="" class="selectpicker  form-control form-control-sm has-info" data-container="body" data-live-search="true" title="Sélectionner un/des apprenant(s) (par défaut : édition pour tous les apprenants)" aria-expanded="false" data-hide-disabled="true" data-actions-box="true" data-virtual-scroll="false">';
        foreach($aApprenant as $iIdApprenant => $sNomPrenomApprenant) {
            $sListeApprenant .= '<option value="'.$iIdApprenant.'">'.$sNomPrenomApprenant.'</option>';
        }
        $sListeApprenant .= '</select>';
        $sListeApprenant .= '<input type="hidden" id="apprenantSelectionne" name="apprenantSelectionne" value="" />';
        $sListeApprenant .= '<input type="hidden" id="apprenantActionForm" name="apprenantActionForm" value="'.implode("*", array_keys($aApprenant)).'" />';
        $sListeApprenant .= '</div>';
        $sListeApprenant .= '</div>';
    }
    else{
        $sListeApprenant = "<div class='mb-2'>Aucun apprenant inscrit à l'action de formation</div>";
    }

    // Gestion de l'édition des feuilles d'émargement liée à l'application Nevea emargement
    $oDynacaseApplication = new \Application();
    if($oDynacaseApplication->exists('NEVEA_EMARGEMENT')){
        // Construction du select pour les semaines
        $html = array ();
        $html[] = "<select id='keysemainePINumerique' name='_keysemainePINumerique'>";
        foreach ($arrSemaine as $key => $value) {
            $html[] = sprintf("<option value='%s'%s>%s</option>", $key, (($key == "0") ? " selected" : ""), $value);
        }
        $html[] = "</select>";
        $sSelectWeek = implode("\n", $html);
        $action->lay->Set("SELECTWEEK", $sSelectWeek);

        $sEditionEmargementNumerique = '<tr class="">';
            $sEditionEmargementNumerique .= '<td>';
                $sEditionEmargementNumerique .= '<div align="center">';
                    $sEditionEmargementNumerique .= '<input type="checkbox" name="chkPresenceIndividuelleNumerique" id="chkPresenceIndividuelleNumerique" />';
                $sEditionEmargementNumerique .= '</div>';
            $sEditionEmargementNumerique .= '</td>';
            $sEditionEmargementNumerique .= '<td>Pr&eacute;sence individuelle hebdomadaire (signature numérique)</td>';
            $sEditionEmargementNumerique .= '<td>Semaine(s)&nbsp;&nbsp;'.$sSelectWeek.'<br />';
            $sEditionEmargementNumerique .= '<input type="checkbox" name="chkPINumeriqueSoustraitance" id="chkPINumeriqueSoustraitance" /> &nbsp;Edition donneur d\'ordre</td>';
        $sEditionEmargementNumerique .= '</tr>';

        $sComplementInfoEmargement = "(vierge)";
                            
    }
    else{
        $sEditionEmargementNumerique = "";
        $sComplementInfoPi = "";
    }

    if(!empty($action->getParam("PROPO_REGLEMENT_INTERIEUR")) && $action->getParam("PROPO_REGLEMENT_INTERIEUR") == "OUI") {
        $sReglementInterieur = '<tr class="">';
        $sReglementInterieur .= '<td>';
        $sReglementInterieur .= '<div align="center">';
        $sReglementInterieur .= '<input type="checkbox" name="chkReglementInterieur" id="chkReglementInterieur" />';
        $sReglementInterieur .= '</div>';
        $sReglementInterieur .= '</td>';
        $sReglementInterieur .= '<td>Règlement intérieur</td>';
        $sReglementInterieur .= '<td></td>';
        $sReglementInterieur .= '</tr>';
    }
    else{
        $sReglementInterieur = "";
    }

    // Passage sur le layout
    $action->lay->Set("REGLEMENT_INTERIEUR", $sReglementInterieur);
    $action->lay->Set("COMPLEMENT_INFO_EMAEGEMENT", $sComplementInfoEmargement);
    $action->lay->Set("EMARGEMENT_HEBDO_NUMERIQUE", $sEditionEmargementNumerique);
    $action->lay->Set("LISTE_APPRENANT", $sListeApprenant);
	$action->lay->Set("DATEDEBUT", $datedebut);
	$action->lay->Set("DATEFIN", $datefin);
	$action->lay->Set("BTNDOSSIER", $bouton);
	$action->lay->Set("ATTESTATIONB96", $tr_b96);
	$action->lay->Set("CLASSSELECT", $classe_select);
    $action->lay->Set("VIEWAIPR", $ScreenAIPR);
    $action->lay->Set("VIEW_ATTEST_RECUPERATION_POINT", $ScreenAttestationRecuperationPoint);
    $action->lay->Set("VIEW_AUTORISATION_CONDUITE", $ScreenAutorisationConduite);
    $action->lay->Set("VIEW_AUTORISATION_CONDUITE_FORMATION", $ScreenAutorisationConduiteFormation);
    $action->lay->Set("VIEW_PERMIS_CONDUIRE_PRATIQUE", $ScreenPermisConduirePratique);
    $action->lay->Set("VIEW_FRANCE_CHIMIE", $ScreenFranceChimie);
    $action->lay->Set("VIEW_ATTEST_SUIVI_FORM_MOBILITE", $ScreenAttestationSuiviFormationMoibilite);
}

function retourneListeApprenantActionFormation($iIdActionForm){
    $aListeApprenant = array();
    $aTableApprenant = array("APPRENANT", "APPRENANTCONV");
    foreach($aTableApprenant as $sTableApprenant) {

        $oApprenant = new \searchDoc("", $sTableApprenant);
        $oApprenant->setObjectReturn();
        $oApprenant->addFilter("app_modid = '%s'", $iIdActionForm);
        $oApprenant->search();

        if($oApprenant->count() > 0) {
            while ($oApprenantDoc = $oApprenant->getNextDoc()) {
                $aListeApprenant[$oApprenantDoc->getRawValue("id")] = $oApprenantDoc->getRawValue("app_appnom")." ".$oApprenantDoc->getRawValue("app_appprenom");
            }
        }
    }

    return $aListeApprenant;
}

function buildTxtSemaine ( $spd ) {

    $lundi = $spd->getMonday();
    $dimanche = $spd->getSunday();
    $txt = sprintf("du %s au %s", $lundi->toShortString(), $dimanche->toShortString());

	return $txt;
}

function buildTxtMois ($moisAnnee , $spd ) {
    $dateDebutMois = "01/".$moisAnnee;
    $dateFinMois = $spd->getLastDayInMonth()."/".$moisAnnee;

    $txt = sprintf("du %s au %s", $dateDebutMois, $dateFinMois);

    return $txt;
}

function buildTR($style, $name, $libelle){
						
	$tr = new StringBuilder();
	$tr->addString("<tr class='%s'>", $style);
	$tr->addString("<td>");
	$tr->addString("<div align='center'>");
	$tr->addString("<input type='checkbox' name='chk%s' id='chk' />", $name, $name);
	$tr->addString("</div>");
	$tr->addString("</td>");
	$tr->addString("<td>%s</td>", $libelle);
	$tr->addString("<td>&nbsp;");
	$tr->addString("</td>");
	$tr->addString("</tr>");
	
	return $tr->buildString("\n");
}
?>
