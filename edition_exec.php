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
set_time_limit(0);

include_once("FDL/Lib.Attr.php");
//include_once ("FDL/Class.DocFam.php");
//include_once ("FDL/Class.Doc.php");
include_once("EXTERNALS/fdl.php");
include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");
include_once("FDL/modcard.php");
//include_once ("FDL/Class.Dir.php");
include_once("FDL/editutil.php");
/**
 * 
 */
include_once("FPDF/modulebarcode.php");
include_once("FPDF/modbs.php");
include_once("FPDF/modconv.php");
include_once("FPDF/modaefrm.php");
include_once("FPDF/modcvl.php");
include_once("FPDF/inscripindiv.php");
include_once("FPDF/modlp.php");
include_once("FPDF/modpi.php");
include_once("FPDF/modpinumerique.php");
include_once("FPDF/modpim.php");
include_once("FPDF/modatfrm.php");
include_once("FPDF/modatfrmaipr.php");
include_once("FPDF/modatfrm_recuperation_point.php");
include_once("FPDF/appcertf.php");
include_once("FPDF/modcalendrier.php");
include_once("FPDF/lettre_relancecertificats.php");
include_once('FPDF/build_listestage.php');
include_once('FPDF/build_autorisationconduite.php');
include_once('FPDF/build_autorisationconduiteformation.php');
include_once('FPDF/build_attestformmobilite.php');
include_once('FPDF/modacertreal.php');
include_once('FPDF/modacertreal2020.php');
include_once('FPDF/cerfa_aipr.php');
// include_once('FPDF/epreuve_pratique_permis.php');
include_once ('FPDF/attestation_france_chimie.php');

//include_once ("FDL/Class.FicheQualiteBuilder.php");
//include_once ("FDL/Class.AttestationB96Builder.php");
//include_once ("FDL/Class.RelanceFormationParticulier.php");

/**
 * 
 */

function edition_exec(&$action)
{

    $debug = false;

    // Action de formation
    $modid = GetHttpVars("modid");

    // Documents
    $codebarre = GetHttpVars("chkCodeBarrre", "");
    $Convocation = GetHttpVars("chkConvocation", "");
    $ConvocationPersonnelle = GetHttpVars("chkConvocationPersonnelle", "");
    $AttEntreeFormation = GetHttpVars("chkAttEntreeFormation", "");
    $FicheInscriptionIndividuelle = GetHttpVars("chkFicheInscriptionIndividuelle", "");
    $Chevalet = GetHttpVars("chkChevalet", "");
    $Bienvenue = GetHttpVars("chkBienvenue", "");
    $EmargermentCollectif = GetHttpVars("chkEmargermentCollectif", "");
    $chkPresenceIndividuelle = GetHttpVars("chkPresenceIndividuelle", "");
    $chkPresenceIndividuelleNumerique = GetHttpVars("chkPresenceIndividuelleNumerique", "");
    $chkPresenceIndividuelleMensuelle = GetHttpVars("chkPresenceIndividuelleMensuelle", "");
    $chkAttestationFormation = GetHttpVars("chkAttestationFormation", "");
    $chkAttestationFormationAipr = GetHttpVars("chkAttestationFormationAipr", "");
    $chkCerfaFormationAipr = GetHttpVars("chkCerfaFormationAipr", "");
    $chkAttestationRecuperationPoint = GetHttpVars("chkAttestationRecuperationPoint", "");
    $chkCertificat = GetHttpVars("chkCertificat", "");
    $chkCalendrier = GetHttpVars("chkCalendrier", "");
    $chkRelanceCertificat = GetHttpVars("chkRelanceCertificat", "");
    $chkFicheQualite = GetHttpVars("chkFicheQualite", "");
    $chkAttestationB96 = GetHttpVars("chkAttestationB96", "");
    $chkRelanceSST = GetHttpVars("chkRelanceSST", "");
    $chkStages = GetHttpVars("chkStage", "");
    $chkAutorisationConduite = GetHttpVars("chkAutorisationConduite", "");
    $chkAutorisationConduiteFormation = GetHttpVars("chkAutorisationConduiteFormation", "");
    $chkEpreuvePratiquePermis = GetHttpVars("chkEpreuvePratiquePermis", "");
    $chkEpreuvePratiquePermisEvt = GetHttpVars("chkEpreuvePratiquePermisEvt", "");
    // France Chimie
    $chkFranceChimie = GetHttpVars("chkFranceChimie", "");
    $chkAttestFormMobilite = GetHttpVars("chkAttestFormMobilite", "");
    $chkCertificationRealisation = GetHttpVars("chkCertificationRealisation", "");
    $chkCertificationRealisation2020 = GetHttpVars("chkCertificationRealisation2020", "");
    $chkReglementInterieur = GetHttpVars("chkReglementInterieur", "");
    $chkTriDoc = GetHttpVars("chkTriDoc", "");


    // Récupère les apprenants
    $aApprenant = explode("*", GetHttpVars("apprenantSelectionne", ""));

    // Documents complémentataires sous-traitance
    $chkAEFSoustraitance = GetHttpVars("chkAEFSoustraitance", "");
    $chkPISoustraitance = GetHttpVars("chkPISoustraitance", "");
    $chkPINumeriqueSoustraitance = GetHttpVars("chkPINumeriqueSoustraitance", "");
    $chkATFRMSoustraitance = GetHttpVars("chkATFRMSoustraitance", "");
    $chkATFRMAiprSoustraitance = GetHttpVars("chkATFRMAiprSoustraitance", "");
    $chkCertificationRealisation2020SousTraitance = GetHttpVars("chkCertificationRealisation2020Soustraitance", "");
    $chkPIMSoustraitance = GetHttpVars("chkPIMSoustraitance", "");

    // Dates
    $keysemaine = GetHttpVars("_keysemaine", "");
    $keysemainePINumerique = GetHttpVars("_keysemainePINumerique", "");
    $keymois = GetHttpVars("_keymois", "");
    $date_debut = GetHttpVars("_date_debut", "");
    $date_fin = GetHttpVars("_date_fin", "");

    $arrFile = array();

    // Connexion DB
    $dbaccess = $action->GetParam("FREEDOM_DB");

    // dossier temporaire
    $filepath = sprintf("%s/FDL/tmp", $action->GetParam("CORE_PUBDIR"));

    // Recupère l'identifiant du site de rattachement
    $oModule = new_Doc("", $modid);
    $iIdSite = $oModule->getRawValue("md_siteid");

    ////////////////////////////////////////////////////////
    // Gestion des documents pour l'action de formation
    ////////////////////////////////////////////////////////

    // Mode de fonctionnement
    $mode = sprintf("file|%s|%s", $modid, mt_rand());

    // Emargement collectif
    if (preg_match("/on/i", $EmargermentCollectif)) {
        $mode_ec = sprintf("%s|%s|%s", $mode, $date_debut, $date_fin);
        $arrFile[] = modlp($action, $mode_ec);
    }

    // Bienvenue
    if (preg_match("/on/i", $Bienvenue)) {
        $arrFile[] = modbs($action, $mode);
    }

    //	Courrier relance SST
    if (preg_match("/on/i", $chkRelanceSST)) {
        $oRelanceSST = new RelanceFormationParticulier($dbaccess, $action->GetParam("CORE_PUBDIR"));
        $relances = $oRelanceSST->buildDocuments($modid);
        $arrFile = array_merge($arrFile, $relances);
    }

    //  Epreuve pratique du Permis de conduire
    if (preg_match("/on/i", $chkEpreuvePratiquePermis)) {
        $arrFile[] = epreuve_pratique_permis($action, $mode);
    }

    //  Epreuve pratique du Permis de conduire avec Evènement
    if (preg_match("/on/i", $chkEpreuvePratiquePermisEvt)) {
        $arrFile[] = epreuve_pratique_permis($action, $mode, true);
    }

    // Attestation France Chimie
    if (preg_match("/on/i", $chkFranceChimie)) {
        $arrFile[] = attestation_france_chimie($action, $mode);
    }


    // Attestation B96
    if (preg_match("/on/i", $chkAttestationB96)) {
        $oAttestationB96 = new AttestationB96Builder($dbaccess, $filepath);
        $attestations = $oAttestationB96->buildDocuments($modid);
        $arrFile[] = $attestations;
    }

    // Convocation
    if (preg_match("/on/i", $Convocation)) {
        $lstFiles = modconv($action, sprintf("%s|%s", $mode, 0));
        foreach ($lstFiles as $k => $file) {
            $arrFile[] = $file;
        }
    }

    // Code barre
    if (preg_match("/on/i", $codebarre)) {
        $arrFile[] = modulebarcode($action, $mode);
    }

    ////////////////////////////////////////////////////////
    // Gestion particuliere du chevalet pour optimiser le papier (2 apprenants par page A4)
    ////////////////////////////////////////////////////////
    // Chevalet
    if (preg_match("/on/i", $Chevalet)) {
        $arrFile[] = modcvl($action, $mode, $aApprenant);
    }

    $arrFileApprenant = array();

    ////////////////////////////////////////////////////////
    // Gestion des documents à l'apprenant
    ////////////////////////////////////////////////////////
    foreach ($aApprenant as $iIdApprenant) {

        // Mode de fonctionnement
        $mode = sprintf("file|%s|%s", $iIdApprenant, mt_rand());

        // Convocation personnelle
        if (preg_match("/on/i", $ConvocationPersonnelle)) {
            $lstFiles = modconv($action, sprintf("%s|%s", $mode, 1));
            foreach ($lstFiles as $k => $file) {
                $arrFileApprenant["convocationPersonnel"][$iIdApprenant][] = $file;
            }
        }

        // Attestation d'entree en formation
        if (preg_match("/on/i", $AttEntreeFormation) && preg_match("/on/i", $chkAEFSoustraitance)) {
            $file = modaefrm($action, $mode . "|X2ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["attestationEntreeFormation"][$iIdApprenant][] = $file;
            }

            $file = modaefrm($action, $mode . "|ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["attestationEntreeFormation"][$iIdApprenant][] = $file;
            }
        } else {
            if (preg_match("/on/i", $AttEntreeFormation)) {
                $file = modaefrm($action, $mode);
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["attestationEntreeFormation"][$iIdApprenant][] = $file;
                }
            }

            if (preg_match("/on/i", $chkAEFSoustraitance)) {
                $file = modaefrm($action, $mode . "|ST");
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["attestationEntreeFormation"][$iIdApprenant][] = $file;
                }
            }
        }

        // Certification de réalisation
        if (preg_match("/on/i", $chkCertificationRealisation)) {
            $arrFileApprenant["certificatRealisation"][$iIdApprenant][] = modacertreal($action, $mode);
        }

        // Certification de réalisation v2020
        if (preg_match("/on/i", $chkCertificationRealisation2020) && preg_match("/on/i", $chkCertificationRealisation2020SousTraitance)) {
            $file = modacertreal2020($action, $mode . "|X2ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["certificatRealisationV2020"][$iIdApprenant][] = $file;
            }
            $file = modacertreal2020($action, $mode . "|ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["certificatRealisationV2020"][$iIdApprenant][] = $file;
            }
        } else {
            if (preg_match("/on/i", $chkCertificationRealisation2020)) {
                $file = modacertreal2020($action, $mode);
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["certificatRealisationV2020"][$iIdApprenant][] = $file;
                }
            }

            if (preg_match("/on/i", $chkCertificationRealisation2020SousTraitance)) {
                $file = modacertreal2020($action, $mode . "|ST");
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["certificatRealisationV2020"][$iIdApprenant][] = $file;
                }
            }
        }

        // Fiche d'inscription individuelle
        if (preg_match("/on/i", $FicheInscriptionIndividuelle)) {
            $arrFileApprenant["inscriptionIndividuelle"][$iIdApprenant][] = inscripindiv($action, $mode);
        }

        // Présence individuelle
        $mode_pi = sprintf("%s|%s", $mode, $keysemaine);
        if (preg_match("/on/i", $chkPresenceIndividuelle) && preg_match("/on/i", $chkPISoustraitance)) {
            $file = modpi($action, $mode_pi . "|X2ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["emargementIndividuelHebdo"][$iIdApprenant][] = $file;
            }
            $file = modpi($action, $mode_pi . "|ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["emargementIndividuelHebdo"][$iIdApprenant][] = $file;
            }
        } else {
            if (preg_match("/on/i", $chkPresenceIndividuelle)) {
                $file = modpi($action, $mode_pi);
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["emargementIndividuelHebdo"][$iIdApprenant][] = $file;
                }
            }

            if (preg_match("/on/i", $chkPISoustraitance)) {
                $file = modpi($action, $mode_pi . "|ST");
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["emargementIndividuelHebdo"][$iIdApprenant][] = $file;
                }
            }
        }

        // Présence individuelle numerique
        $mode_pi = sprintf("%s|%s", $mode, $keysemainePINumerique);
        if (preg_match("/on/i", $chkPresenceIndividuelleNumerique) && preg_match("/on/i", $chkPINumeriqueSoustraitance)) {
            $file = modpinumerique($action, $mode_pi . "|X2ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["emargementIndividuelHebdoNumerique"][$iIdApprenant][] = $file;
            }
            $file = modpinumerique($action, $mode_pi . "|ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["emargementIndividuelHebdoNumerique"][$iIdApprenant][] = $file;
            }
        } else {
            if (preg_match("/on/i", $chkPresenceIndividuelleNumerique)) {
                $file = modpinumerique($action, $mode_pi);
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["emargementIndividuelHebdoNumerique"][$iIdApprenant][] = $file;
                }
            }

            if (preg_match("/on/i", $chkPINumeriqueSoustraitance)) {
                $file = modpinumerique($action, $mode_pi . "|ST");
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["emargementIndividuelHebdoNumerique"][$iIdApprenant][] = $file;
                }
            }
        }

        // Présence individuelle mensuelle
        $mode_pim = sprintf("%s|%s", $mode, $keymois);
        if (preg_match("/on/i", $chkPresenceIndividuelleMensuelle) && preg_match("/on/i", $chkPIMSoustraitance)) {
            $file = modpim($action, $mode_pim . "|X2ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["emargementIndividuelMensuel"][$iIdApprenant][] = $file;
            }
            $file = modpim($action, $mode_pim . "|ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["emargementIndividuelMensuel"][$iIdApprenant][] = $file;
            }
        } else {
            if (preg_match("/on/i", $chkPresenceIndividuelleMensuelle)) {
                $file = modpim($action, $mode_pim);
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["emargementIndividuelMensuel"][$iIdApprenant][] = $file;
                }
            }

            if (preg_match("/on/i", $chkPIMSoustraitance)) {
                $file = modpim($action, $mode_pim . "|ST");
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["emargementIndividuelMensuel"][$iIdApprenant][] = $file;
                }
            }
        }

        // Attestation de formation
        if (preg_match("/on/i", $chkAttestationFormation) && preg_match("/on/i", $chkATFRMSoustraitance)) {
            $file = modatfrm($action, $mode . "|X2ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["attestationFinFormation"][$iIdApprenant][] = $file;
            }

            $file = modatfrm($action, $mode . "|ST");
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["attestationFinFormation"][$iIdApprenant][] = $file;
            }
        } else {
            if (preg_match("/on/i", $chkAttestationFormation)) {
                $file = modatfrm($action, $mode);
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["attestationFinFormation"][$iIdApprenant][] = $file;
                }
            }

            if (preg_match("/on/i", $chkATFRMSoustraitance)) {
                $file = modatfrm($action, $mode . "|ST");
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["attestationFinFormation"][$iIdApprenant][] = $file;
                }
            }
        }

        // Attestation de formation AIPR
        if (preg_match("/on/i", $chkAttestationFormationAipr) && preg_match("/on/i", $chkATFRMAiprSoustraitance)) {
            $file = modatfrmaipr($action, $mode . "|X2ST", true);
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["attestationAipr"][$iIdApprenant][] = $file;
            }

            $file = modatfrmaipr($action, $mode . "|ST", true);
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["attestationAipr"][$iIdApprenant][] = $file;
            }
        } else {
            if (preg_match("/on/i", $chkAttestationFormationAipr)) {
                $file = modatfrmaipr($action, $mode, true);
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["attestationAipr"][$iIdApprenant][] = $file;
                }
            }

            if (preg_match("/on/i", $chkATFRMAiprSoustraitance)) {
                $file = modatfrmaipr($action, $mode . "|ST", true);
                if (mb_strlen($file) > 0) {
                    $arrFileApprenant["attestationAipr"][$iIdApprenant][] = $file;
                }
            }
        }

        // Cerfa AIPR
        if (preg_match("/on/i", $chkCerfaFormationAipr)) {
            $file = cerfa_aipr($action, $mode);
            if (mb_strlen($file) > 0) {
                $arrFileApprenant["cerfaAipr"][$iIdApprenant][] = $file;
            }
        }

        // Attestation formation récupération de point
        if (preg_match("/on/i", $chkAttestationRecuperationPoint)) {
            $arrFileApprenant["attestationRecuperationPoint"][$iIdApprenant][] = modatfrm_recuperation_point($action, $mode);
        }

        // Certificat
        if (preg_match("/on/i", $chkCertificat)) {
            $aTempFile = appcertf($action, $mode);
            if (!empty($aTempFile)) {
                $arrFileApprenant["certificat"][$iIdApprenant][] = $aTempFile;
            }
        }

        // Calendrier
        if (preg_match("/on/i", $chkCalendrier)) {
            $arrCalendrier = modcalendrier($action, $mode);
            foreach ($arrCalendrier as $k => $file) {
                $arrFileApprenant["calendrier"][$iIdApprenant][] = $file;
            }
            //$arrFileApprenant = array_merge($arrFileApprenant, $arrCalendrier);
        }

        // Relance certificat
        if (preg_match("/on/i", $chkRelanceCertificat)) {
            $arrRelancesCertificat = lettre_relancecertificats($action, $mode);
            foreach ($arrRelancesCertificat as $k => $file) {
                $arrFileApprenant["relanceCertificat"][$iIdApprenant][] = $file;
            }
            //$arrFileApprenant = array_merge($arrFileApprenant, $arrRelancesCertificat);
        }

        // Fiche qualité
        if (preg_match("/on/i", $chkFicheQualite)) {
            $oFicheQualiteBuilder = new FicheQualiteBuilder($dbaccess, $filepath);
            $fiches_qualite = $oFicheQualiteBuilder->buildDocument($iIdApprenant);
            $arrFileApprenant["ficheQualite"][$iIdApprenant][] = $fiches_qualite;
        }


        //  Liste des stages en entreprise
        if (preg_match("/on/i", $chkStages)) {
            $liste_stages = build_liststage($action, $iIdApprenant);
            foreach ($liste_stages as $k => $file) {
                $arrFileApprenant["stage"][$iIdApprenant][] = $file;
            }
            //$arrFileApprenant = array_merge($arrFileApprenant, $liste_stages);
        }

        //  Autorisation de conduite
        if (preg_match("/on/i", $chkAutorisationConduite)) {
            $listeAutorisationConduite = build_autorisationconduite($action, $iIdApprenant);
            foreach ($listeAutorisationConduite as $k => $file) {
                $arrFileApprenant["autorisationConduite"][$iIdApprenant][] = $file;
            }
            //$arrFileApprenant = array_merge($arrFileApprenant, $listeAutorisationConduite);
        }

        //  Autorisation de conduite formation
        if (preg_match("/on/i", $chkAutorisationConduiteFormation)) {
            $listeAutorisationConduiteFormation = build_autorisationconduiteformation($action, $iIdApprenant);
            foreach ($listeAutorisationConduiteFormation as $k => $file) {
                $arrFileApprenant["autorisationConduiteFormation"][$iIdApprenant][] = $file;
            }
            //$arrFileApprenant = array_merge($arrFileApprenant, $listeAutorisationConduiteFormation);
        }

        //  Attestation de suivi de la formation à la mobilité
        if (preg_match("/on/i", $chkAttestFormMobilite)) {
            $listeAttestFormMobilite = build_attestformmobilite($action, $iIdApprenant);
            foreach ($listeAttestFormMobilite as $k => $file) {
                $arrFileApprenant["attestationFormationMobilite"][$iIdApprenant][] = $file;
            }
            //$arrFileApprenant = array_merge($arrFileApprenant, $listeAttestFormMobilite);
        }


        // Règlement intérieur
        if (preg_match("/on/i", $chkReglementInterieur)) {
            $reglementInterieur = new reglementInterieur($iIdSite);
            $reglementInterieur->cvdbaccess = $dbaccess;
            $reglementInterieur->cvidsite = $iIdSite;
            $reglementInterieur->cvpath = $filepath;
            $arrFileApprenant["reglementInterieur"][$iIdApprenant][] = $reglementInterieur->buildDocument();
        }
    }


    // Tri selon le type
    if ($chkTriDoc == "typeDoc") {
        foreach ($arrFileApprenant as $sTypeDoc => $aApprenantListeDoc) {
            foreach ($aApprenantListeDoc as $iIdApprenantListeDoc => $aApprenantListeFichier) {
                foreach ($aApprenantListeFichier as $sFichier) {
                    $arrFile[] = $sFichier;
                }
            }
        }
    } else {
        foreach ($aApprenant as $iIdApprenant) {
            foreach ($arrFileApprenant as $sTypeDoc => $aApprenantListeDoc) {
                foreach ($aApprenantListeDoc as $iIdApprenantListeDoc => $aApprenantListeFichier) {
                    if ($iIdApprenant == $iIdApprenantListeDoc) {
                        foreach ($aApprenantListeFichier as $sFichier) {
                            $arrFile[] = $sFichier;
                        }
                    }
                }
            }
        }
    }

    //exit(0);

    //echo "<pre>"; print_r($arrFileApprenant); echo "</pre>";
    //echo "<pre>"; print_r($arrFile); echo "</pre>";exit(0);

    // TESTS
    if ($debug == true) {
        print "MODE DEBUG<br>";
        print "<pre>";
        print_r($arrFile);
        print "</pre>";
        print "Fin du tableau<br>";
        return false;
    }

    // Concatener les fichiers
    $pdf_concat = new concat_pdf(); //'L', 'mm', array(86, 55)
    $pdf_concat->setFiles($arrFile);
    $pdf_concat->concat();

    // Supprimer les fichiers du disque
    foreach ($arrFile as $k => $v) {
        @unlink($v);
    }

    //  Generer le nouveau pdf
    echo $pdf_concat->Output(sprintf("dossier_af_%s.pdf", $modid));
    exit();
}
