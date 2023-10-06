<?php

	session_start();
	$sessioneId = session_id();
	set_time_limit ( 20 );
	ini_set('display_errors', false ); 
	
	global $myPath,$default,$sitoTemplate,$isGoAction,$categories, $dbName, $affiliations,$subcategories, $typologies;
	$pathSite = getenv( 'pathSite' );
	
    $cartellaLavoro = !empty( $_GET['folder'] ) ? $_GET['folder'] : 'miglioreprezzo';
    if( empty( $_GET['db'] ) ) {
        die('Inserisci il nome del db delle categorie');
    }
    $dbName = $_GET['db'];
    
	$siteObjectsElements  = new stdClass();
    $siteObjectsElements->config = new stdClass();
    $siteObjectsElements->config->cartellaXmlTemplate  = __DIR__.'/../../app/Resources/layouts/'.$cartellaLavoro;
    $siteObjectsElements->config->cartellaLetturaTemplate  = __DIR__.'/../../app/Resources/views/'.$cartellaLavoro;
    $siteObjectsElements->config->pathSviluppoTemplate = __DIR__.'/../../app/Resources/views/'.$cartellaLavoro;
    
    $user = 'myuser';
    $pass = 'secret';
    $db = new PDO('mysql:host=mysql;', $user, $pass);
            
    // $sth = $db->prepare("select * from $dbName.categories order by name");
    // $sth->execute();
    // $aCategories = $sth->fetchAll( PDO::FETCH_OBJ );
    
    $categories = array();
    foreach ( $aCategories As $cat ) {
        $categories[$cat->id] =  $cat ;
    }
    
            
    // $sth = $db->prepare("select * from $dbName.subcategories order by name");
    // $sth->execute();
    // $aSubcategories = $sth->fetchAll( PDO::FETCH_OBJ );
    
    $subcategories = array();
    foreach ( $aSubcategories As $cat ) {
        $subcategories[$cat->id] =  $cat ;
    }
    
            
    // $sth = $db->prepare("select * from $dbName.typologies order by name");
    // $sth->execute();
    // $aTypologies = $sth->fetchAll( PDO::FETCH_OBJ );
    
    $typologies = array();
    foreach ( $aTypologies As $cat ) {
        $typologies[$cat->id] =  $cat ;
    }
    
    // $sth = $db->prepare("select idAffiliation as id,name from $dbName.affiliation order by name");
    // $sth->execute();
    // $aAffiliations = $sth->fetchAll( PDO::FETCH_OBJ );
    
    $affiliations = array();
    foreach ( $aAffiliations As $aff ) {
        $affiliations[$aff->id] =  $aff ;
    }
    
	$sessionActive = 1;
	$adminLevel = 1;
	$isGestione = false;
	
	if(!$sessionActive && $adminLevel != 1) {
		die("Non ci provare ad entrare...");
	}
	
	$body = '';
	$colonDx = '';

	if ( !empty( $_POST["actionForm"] ) && $_POST["actionForm"] == "getSelectList") {
	  $xml = simplexml_load_file($siteObjectsElements->config->cartellaXmlTemplate."/functions.xml");
		$funzione = explode( "-" , $_POST["funzioni"] );
		echo '
			<div id="close">X</div>
			<select multiple name="mySelect" id="mySelect">';
			foreach ($xml as $nodo){
				$select = in_array( $nodo , $funzione ) ? "selected" : "";
				echo '
			 		<option value="'.$nodo.'" '.$select.'>'.$nodo.'
				';
				$select='';
			}
		echo '
			</select>
			<input type="button" value="Aggiungi funzioni" id="addF">';
			die();
	
	} else if ( !empty( $_POST["actionForm"] ) && $_POST["actionForm"] == "addTpl") {
		$val = '.tpl';
		$nomeModulo = @preg_match("#$val#",$_POST['nomeModulo']) ? $_POST['nomeModulo'] : $_POST['nomeModulo'].".tpl";
		$nomeModulo = trim( $nomeModulo );
		$myPathBox = $siteObjectsElements->config->pathSviluppoTemplate."/".$nomeModulo;
		$varModulo = str_replace(array(".html.twig",'.tpl'),"",$nomeModulo);
		$body.= '{$fetch_'.$varModulo.'}';
		$f = fopen($myPathBox, "w");
		fwrite($f,$body);
		fclose($f);
		
		if (chmod($myPathBox,0777)) {
			$modelDefault = file_get_contents( $siteObjectsElements->config->pathSiteServer.'/lib/TemplatesObjects/ModelDefault.txt' );
			$nameClass = ucfirst( str_replace(array(".html.twig",'.tpl'), '', $nomeModulo ) );
			$modelDefault = str_replace('[nameClass]', $nameClass.'_TO', $modelDefault );
			$nameFileClass = ucfirst( str_replace(array(".html.twig",'.tpl'), '_TO.class.php', $nomeModulo ) );
			
			$myPathBox = $siteObjectsElements->config->pathSiteServer.'/lib/TemplatesObjects/'.$nameFileClass;
			$f = fopen($myPathBox, "w");
			fwrite($f,$modelDefault);
			fclose($f);
			if ( chmod($myPathBox,0777) )
				header("Location: /templatemanager/index.php?addTpl=1&respNM=1");
			else
				header("Location: /templatemanager/index.php?addTpl=1&respNM=2");
		} else {
			header("Location: /templatemanager/index.php?addTpl=1&respNM=3");
		}
	}else if ( !empty( $_POST["actionForm"] ) && $_POST["actionForm"]  == "addProject") {
		$val = '.xml';
		$nomeProject = @preg_match("#$val#",$_POST['nomeProject']) ? $_POST['nomeProject'] : $_POST['nomeProject'].".xml";
		$file = $siteObjectsElements->config->cartellaXmlTemplate."/".$nomeProject;
		$body.= '<?xml version="1.0" encoding="UTF-8"?>';
		$body.= '<root name="root"></root>';
		$f = fopen($file, "w");
		fwrite($f,$body);
		fclose($f);
	} 

	if ( !empty( $_POST["actionStruttura"] ) && $_POST["actionStruttura"] == "cambiaFileStruttura"){
		$_SESSION["nomeFileStruttura"] =	$_POST["nomeFileStruttua"];
	} else if ( empty( $_SESSION["nomeFileStruttura"] ) ) {
		$_SESSION["nomeFileStruttura"] = "strutturaHome.xml";
	}

	$siteObjectsElements->config->nomeFileStruttura = $_SESSION["nomeFileStruttura"];
	$xmlFile = includeFolder($siteObjectsElements->config->cartellaXmlTemplate);
	
		
if ( !empty( $_GET["gestioneStruttura"] ) || !empty( $_POST["actionStruttura"] ) ) {
	$isGestione = true;
	$xml = simplexml_load_file($siteObjectsElements->config->cartellaXmlTemplate."/".$siteObjectsElements->config->nomeFileStruttura);
	$struttua = creaXML($xml);
	$elencoFile = sfogliaCartelle($siteObjectsElements->config->pathSviluppoTemplate,2,$isGestione,$_SESSION["nomeFileStruttura"],$siteObjectsElements);
	$colonDx .= '
	<ul id="dhtmlgoodies_tree2" class="dhtmlgoodies_tree">
		<li id="node0" noDrag="true" noSiblings="true" noDelete="true" noRename="true"><a href="#">Root node</a>
			<ul>';
				$colonDx .= $struttua.'
			</ul>
		</li>
	</ul>
	<form>
	<input type="button" onclick="saveMyTree()" value="Save">
	</Form>
	<script type="text/javascript">
	treeObj = new JSDragDropTree();
	treeObj.setTreeId(\'dhtmlgoodies_tree2\');
	treeObj.setMaximumDepth(100);
	treeObj.setMessageMaximumDepthReached(\'Maximum depth reached\'); // If you want to show a message when maximum depth is reached, i.e. on drop.
	treeObj.initTree();
	treeObj.expandAll();
	</script>
	<a href="#" onclick="treeObj.collapseAll()">Collapse all</a> |
	<a href="#" onclick="treeObj.expandAll()">Expand all</a>
	<!-- Form - if you want to save it by form submission and not Ajax -->
	<form name="myForm" method="post" action="saveNodes.php">
		<input type="hidden" name="saveString">
	</form>';

} else if ( !empty( $_GET["addTpl"] ) ){
	if( !empty( $_GET["respNM"] ) && $_GET["respNM"] == 1 )
		$resp = "File creato correttamente";
	
	else if( !empty( $_GET["respNM"] ) && $_GET["respNM"] == 2 )
		$resp = "File TPL creato correttamente! File PHP NON CREATO!" ;
	
	else if( !empty( $_GET["respNM"] ) && $_GET["respNM"] == 3 )
		$resp = '';
		$resp = "Problemi nella crezione del file" ;
		$colonDx .= "
		<div style='border:1px solid #333;padding:10px;margin-top:15px;'>
			<div style='border-bottom:1px solid #333;width:90%;margin-bottom:15px;font-size:18px'>Crea Nuovo File Tpl</div>
			<div style='color:#ff0000;font-weight:bold;font-size:19px' >".$resp."</div>
			<form action='index.php' method='POST' name='formNewModulo'>
				<input type='hidden' name='actionForm' value='addTpl'>
				<input type='text' value='' name='nomeModulo' size='70' style='height;15px;padding:5px;'>
				<input type='submit' value='Crea Modulo' style='height:28px;'>
			</form>
		</div>";

} else if ( !empty( $_GET["addProject"] ) ) {
	$resp = isset( $_GET["respNM"] ) && $_GET["respNM"]  == 1 ? "File creato correttamente" : "Problemi nella crezione del file" ;
	$colonDx .= "
	<div style='border:1px solid #333;padding:10px;margin-top:15px;'>
		<div style='border-bottom:1px solid #333;width:90%;margin-bottom:15px;font-size:18px'>Crea Nuovo Progetto</div>
		<div style='color:#ff0000;font-weight:bold;font-size:19px' >".$resp."</div>
		<form action='index.php' method='POST' name='formNewProject'>
			<input type='hidden' name='actionForm' value='addProject'>
			<input type='text' value='' name='nomeProject' size='70' style='height;15px;padding:5px;'>
			<input type='submit' value='Crea Progetto' style='height:28px;'>
		</form>
	</div>";

} else if ( !empty( $_GET["vediTpl"] ) ){
	$text = file_get_contents( $siteObjectsElements->config->cartellaLetturaTemplate."/".$_GET['tpl']."");
	$colonDx .= "<pre>".$text."</pre>";
}

	$popup = '<div id="popupFunzioni"></div>';
	$body .= '
		<div id="taskbar">
			<div class="label">esci</div>
			<div class="icona"><img src="/templatemanager/img/exit.png" /></div>
			<div class="label" onclick="location.href=\'/index.html?KEY1=fhw98cnr9348rco394trb9c234rb&KEY2=xadoyvlxn0r9430rc74039&file='.$_SESSION["nomeFileStruttura"].'\'">Guarda Anteprima</div>
			<div class="icona" onclick="location.href=\'/index.html?KEY1=fhw98cnr9348rco394trb9c234rb&KEY2=xadoyvlxn0r9430rc74039&file='.$_SESSION["nomeFileStruttura"].'\'"><img src="/templatemanager/img/anteprima.png" /></div>
			<div class="label" onclick="location.reload();">aggiorna</div>
			<div class="icona" onclick="location.reload();"><img src="/templatemanager/img/refresh.png" /></div>';
			if ($isGestione) {
				$body .= '
				<div class="label">Salva</div>
				<div class="icona">
					<img src="/templatemanager/img/save.png" onclick="saveMyTree()" />
				</div>';
			}
			
	$body.= '
				<div class="clear"></div>
		  </div>
		  <div id="mainscreen">
				<div id="leftcontainer">
					 <div id="filelist-container">
						<div class="boxtitle">File</div>
							<div class="boxlistcontent">
								<ul id="fileboxlist">
									 '.sfogliaCartelle($siteObjectsElements->config->pathSviluppoTemplate,1,$isGestione,$_SESSION["nomeFileStruttura"],$siteObjectsElements).'
								</ul>
						  </div>
					 </div>
				</div>
				<div id="rightcontainer">
					<div id="treerightcontent">
							<div id="treecontframe">
								<div class="boxtitle">Tree</div>
								<div style=" font-size: 14px;height: 800px;overflow: auto;padding: 20px;">'.$colonDx.'</div>
							</div>
					</div>
				</div>
				<div class="clear"></div>
		  </div>
		  <div id="messagetoolbar">
				<div class="icona">
                <!--
					<table><tr><td>
						<a href="/templatemanager/index.php?addTpl=1">
							<div><img src="/templatemanager/img/tpl.png" title="Nuovo tpl" alt="Nuovo tpl" width="64"/></div>
							<div>Nuovo Tpl</div>
						</a>
					</td><td style="padding-left:25px">
						<a href="/templatemanager/index.php?addProject=1">
							<div><img src="/templatemanager/img/Filetype-XML-icon.png" title="Nuovo Progetto" alt="Nuovo Progetto" width="64"/></div>
							<div>Nuovo Progetto</div>
						</a>
					</td></tr></table>-->
				</div> 
				<div id="selectProject">
					 <div class="label">Progetto: </div>
					 <div class="label">
					 	<form action="index.php?folder='.$cartellaLavoro.'&db='.$dbName.'" method="POST">
					 		<input type="hidden" name="actionStruttura" value="cambiaFileStruttura">
						  <select name="nomeFileStruttua">
								'.getFileStruttura($xmlFile,$siteObjectsElements->config->nomeFileStruttura).'
							</select>
							<input type="submit" value="Gestisci Template">
						</form>
					 </div>
				</div>
			</div>
            
';

	echo'
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
	<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
		<title>Template Manager Templates</title>
        
		<script type="text/javascript" src="js/jquery.min.js"> </script>
        <script>
             var jq = jQuery.noConflict();
        </script>
        <script type="text/javascript" src="js/jClass.js"> </script>

		<link rel="stylesheet" href="/templatemanager/css/stile.css" type="text/css" />
		<script type="text/javascript" src="js/ajax.js"></script>
		<script type="text/javascript" src="js/context-menu.js"></script>
		<!-- IMPORTANT! INCLUDE THE context-menu.js FILE BEFORE drag-drop-folder-tree.js -->
		<script type="text/javascript" src="js/drag-drop-folder-tree.js">
		</script>
		<link rel="stylesheet" href="css/drag-drop-folder-tree.css" type="text/css"></link>
		<link rel="stylesheet" href="css/context-menu.css" type="text/css"></link>
		<style type="text/css">
			/* CSS for the demo */
			img{
				border:0px;
			}
		</style>
		<script type="text/javascript">
		//--------------------------------
		// Save functions
		//--------------------------------
		var ajaxObjects = new Array();
		// Use something like this if you want to save data by Ajax.
		function saveMyTree(tipo) {
				saveString = treeObj.getNodeOrders();
				var ajaxIndex = ajaxObjects.length;
				ajaxObjects[ajaxIndex] = new sack();
				ajaxObjects[ajaxIndex].method = "POST";
				ajaxObjects[ajaxIndex].setVar("saveString", saveString);
				var url = \'saveNodes.php?folder='.$cartellaLavoro.'&db='.$dbName.'&saveString=\' + saveString;
				ajaxObjects[ajaxIndex].requestFile = url;	// Specifying which file to get
				ajaxObjects[ajaxIndex].onCompletion = function() { saveComplete(ajaxIndex,tipo); } ;	// Specify function that will be executed after file has been found
				ajaxObjects[ajaxIndex].runAJAX();		// Execute AJAX function
				
		}
		function saveComplete(index,tipo)	{
			alert(ajaxObjects[index].response);
			if(tipo == 2) {
				location.href = "/templatemanager/index.php?folder='.$cartellaLavoro.'&db='.$dbName.'&gestioneStruttura=1";
			}
		}
	
		//Call this function if you want to save it by a form.
		function saveMyTree_byForm()	{
			document.myForm.elements[\'saveString\'].value = treeObj.getNodeOrders();
			document.myForm.submit();
		}
	
		</script>
	</head>';
	echo'<body>'.$popup.$body.'</body>
	</html>';

	function sfogliaCartelle($dir,$tipo,$isGestione,$templateAttuale,$siteObjectsElements) {
		$myArray =  array();
		$body = '';
		$myDir = '';
		$iconaAdd = '';
		$fileRoot = includeFolder($dir,false);
		/*
		foreach($fileRoot AS $myRoot) {
			//$iconaAdd = $isGestione ? "<img src='/templatemanager/img/Add-Folder-icon.png'  width='17' title='Aggiungi il file alla struttura' alt='Aggiungi il file alla struttura' />" :"" ;
			if($tipo == 1) {
				$body .="
				<li class='itemModuli'>
					<a href='".$siteObjectsElements->config["cartellaLetturaTemplate"]."/templates/".$myDir."/".$myRoot."' target='_blank'>".$myRoot."</a>
					".$iconaAdd."
				</li>";
			} else {
				$myArray[] = $myRoot;
			}
		}*/
		//$dirRoot = includeFolder($default->pathSviluppoTemplate,true);
		
		foreach($fileRoot AS $myFile) {
            $myFile = str_replace(array(".html.twig",'.tpl'),array("",""),$myFile);
			if($tipo == 1 && $myFile != 'base' && $myFile != 'index' ) {
				$iconaAdd = $isGestione ? "<img onclick='treeObj.addItem(\"".str_replace(array(".html.twig",'.tpl'),array("",""),$myFile)."|".rand()."\");' src='/templatemanager/img/Add-Folder-icon.png'  width='17' title='Aggiungi il file alla struttura' alt='Aggiungi il file alla struttura' />" :"" ;
				$body .="
				<li id='".$myFile."' class='itemModuli' style='margin-left:10px;cursor:pointer' >
					<a href='".$siteObjectsElements->config->cartellaLetturaTemplate."/templates/".$myDir."/".$myFile."' target='_blank'>".$myFile."</a>
					".$iconaAdd."
				</li>";
			} else if($tipo == 2 && $myDir == "box" && $myFile != 'base' && $myFile != 'index'  ){
				$myArray[] = $myFile;
			}
		}
		
		$val2 = "sviluppo";
		$dirRoot =  !preg_match("#$val2#",strtolower($templateAttuale)) ? array("") : array("boxSviluppo");

		
		/*		
//		<img onclick='treeObj.addItem(\"".str_replace(".tpl","",$myFile)."\");' src='/templatemanager/img/Document-Delete-icon.png'  width='18' title='Elimina il file dal progetto' alt='Elimina il file dal progetto'  />
		foreach($dirRoot AS $myDir) {
			if($tipo == 1) {
				$body .="<div class='contElement' style='color:#ff0000'>".$myDir."</div>";
			}
			$fileRoot = includeFolder($siteObjectsElements->config["pathSviluppoTemplate"].$myDir."",false);
			if ($fileRoot) {
				foreach($fileRoot AS $myFile) {
					if($tipo == 1) {
						$iconaAdd = $isGestione ? "<img onclick='treeObj.addItem(\"".str_replace(".tpl","",$myFile)."|".rand()."\");' src='/templatemanager/img/Add-Folder-icon.png'  width='17' title='Aggiungi il file alla struttura' alt='Aggiungi il file alla struttura' />" :"" ;
						$body .="
						<li id='".$myFile."' class='itemModuli' style='margin-left:10px;cursor:pointer' >
							<a href='".$siteObjectsElements->config["cartellaLetturaTemplate"]."/templates/".$myDir."/".$myFile."' target='_blank'>".$myFile."</a>
							".$iconaAdd."
						</li>";
					} else if($tipo == 2 && $myDir == "box"){
						$myArray[] = $myFile;
					}
				}
			}
		}*/
		return $tipo == 1 ? $body : $myArray;
	}

	function includeFolder( $folder, $directory = false ) {
		$dir = scandir($folder);
		$x = 0;
		foreach( $dir as $f ) {
			if( $f == "." || $f == "..") {
				continue;
			}
			if ( is_dir( $folder."/".$f ) && $directory == true ) {
				$arrayfile[] = $f;
			} else if ( !is_dir( $folder."/".$f ) && $directory == false ){
				$arrayfile[] = $f;
			}
			
			$x++;
		}
		if( !is_null($arrayfile)) {
			sort( $arrayfile );
		}
		
		return $arrayfile;
	}

	function getFileStruttura($xmlFile,$nomeFileStruttura) {
		$body = '';
		foreach($xmlFile AS $myXml){
			$val = '.save';
			if (strtolower($myXml) != "functions.xml" && !preg_match("#$val#",strtolower($myXml))) {
				$select = strtolower($nomeFileStruttura) == strtolower($myXml) ? 'selected': '';
				$body .= '<option value="'.$myXml.'" '.$select.'>'.str_replace("struttura","",$myXml).'</option>';
			}
		}
		return $body;
	}

	function creaXML( $xml ){
		$str = '';
		foreach ($xml as $nodo){
			$str .= getLi($nodo);
		}
		return $str;
	}
	
    /**
     * Metodo che aggiunge i nodi 
     * @param type $nodo
     * @return string
     */
	function getLi( $nodo ){
        global $categories, $dbName, $affiliations, $subcategories, $typologies;
        
		$nome = explode( "|", $nodo['name'] );        
        
        $styleVarAttrAjax = "background: #e4a763;padding: 2px 5px;font-size:12px;";
        $styleAssign = "background: #E46363;padding: 2px 5px;font-size:12px;";
        $styleCores  = "background: #14AF14;padding: 2px 5px;font-size:12px;";
        $styleAjax  = "background: #ff6600;padding: 2px 5px;font-size:12px;";
        $styleCategory  = "background: #369c90;color:#000;padding: 2px 5px;font-size:12px;";
        $styleSubcategory  = "background: #369c90;color:#000;padding: 2px 5px;font-size:12px;";
        $styleTypology  = "background: #369c90;color:#000;padding: 2px 5px;font-size:12px;";
        $styleLimit  = "background: #ffd900;padding: 2px 5px;font-size:12px;";
        $styleAffiliation  = "background: #d2b7ac;padding: 2px 5px;font-size:12px;";
        $styleTrademark  = "background: #8d5f94;padding: 2px 5px;font-size:12px;";
        
        $assignView  = empty( $nodo['assign'] ) || $nodo['assign'] == 'null' ? '<span id="'.$nome[0].$nome[1].'_assignInfo" style="'.$styleAssign.'"></span>' :  '- <span id="'.$nome[0].$nome[1].'_assignInfo" style="'.$styleAssign.'">Assign: '.$nodo['assign'].'</span>';
        $varAttrAjaxView  = empty( $nodo['varAttrAjax'] ) || $nodo['varAttrAjax'] == 'null' ? '<span id="'.$nome[0].$nome[1].'_varAttrAjaxInfo" style="'.$styleVarAttrAjax.'"></span>' :  '- <span id="'.$nome[0].$nome[1].'_varAttrAjaxInfo" style="'.$styleVarAttrAjax.'">varAttrAjax: '.$nodo['varAttrAjax'].'</span>';
        $viewCores   = empty( $nodo['cores'] )  || $nodo['cores']  == 'null' ? '<span id="'.$nome[0].$nome[1].'_coresInfo" style="'.$styleCores.'"></span>' :  ' - <span id="'.$nome[0].$nome[1].'_coresInfo" style="'.$styleCores.'"> Core: '. str_replace(' ', ' - ',$nodo['cores']).'</span>';                
        $viewCategoryNews   = empty( $nodo['categoryNews'] )  || $nodo['categoryNews']  == 'null' ? '<span id="'.$nome[0].$nome[1].'_categoryNewsInfo" style="'.$styleCategory.'"></span>' :  ' - <span id="'.$nome[0].$nome[1].'_categoryNewsInfo" style="'.$styleCategory.'"> Cat: '. str_replace(' ', ' ',$categories[(int)$nodo['categoryNews']]->name).'</span>';
        $viewSubcategory   = empty( $nodo['subcategory'] )  || $nodo['subcategory']  == 'null' ? '<span id="'.$nome[0].$nome[1].'_subcategoryInfo" style="'.$styleSubcategory.'"></span>' :  ' - <span id="'.$nome[0].$nome[1].'_subcategoryInfo" style="'.$styleSubcategory.'"> Sub.cat: '. str_replace(' ', ' ',$subcategories[(int)$nodo['subcategory']]->name).'</span>';
        $viewTypology   = empty( $nodo['typology'] )  || $nodo['typology']  == 'null' ? '<span id="'.$nome[0].$nome[1].'_typologyInfo" style="'.$styleTypology.'"></span>' :  ' - <span id="'.$nome[0].$nome[1].'_typologyInfo" style="'.$styleTypology.'"> Typol.: '. str_replace(' ', ' ',$typologies[(int)$nodo['typology']]->name).'</span>';
        $viewLimitNews   = empty( $nodo['limitNews'] )  || $nodo['limitNews']  == 'null' ? '<span id="'.$nome[0].$nome[1].'_limitNewsInfo" style="'.$styleLimit.'"></span>' :  ' - <span id="'.$nome[0].$nome[1].'_limitNewsInfo" style="'.$styleLimit.'"> Limit:: '. str_replace(' ', ' - ',$nodo['limitNews']).'</span>';
        $viewAffiliation   = empty( $nodo['affiliation'] )  || $nodo['affiliation']  == 'null' ? '<span id="'.$nome[0].$nome[1].'_affiliationInfo" style="'.$styleAffiliation.'"></span>' :  ' - <span id="'.$nome[0].$nome[1].'_affiliationInfo" style="'.$styleAffiliation.'"> Affiliazione: '. str_replace(' ', ' - ',$affiliations[(int)$nodo['affiliation']]->name).'</span>';
        $viewTrademark    = empty( $nodo['trademark'] )  || $nodo['trademark']  == 'null' ? '<span id="'.$nome[0].$nome[1].'_trademarkInfo" style="'.$styleTrademark.'"></span>' :  ' - <span id="'.$nome[0].$nome[1].'_trademarkInfo" style="'.$styleTrademark.'"> Marchio: '. $nodo['trademark'].'</span>';
        
        switch( $nodo['ajax'] ) {
            case 'null':
            case 0:
                $valueAjax = 'Modalità: Sincrono';
            break;
            case 1:
                $valueAjax = 'Modalità: Asincrono (ON LOAD PAGE)';
            break;
            case 2:
                $valueAjax = 'Modalità: Asincrono (ON EVENT)';
            break;
        }
        $viewAjax   = '<span id="'.$nome[0].$nome[1].'_ajaxInfo" style="'.$styleAjax.'"> '.$valueAjax.'</span>';
        
		$str = '<li id="'.$nodo['name'].'" noRename="true">';
		$str .= '<a href="#" data-varTpl="'.$nodo['assign'].'" data-varAttrAjax="'.$nodo['varAttrAjax'].'" data-cores="'.$nodo['cores'].'" data-ajax="'.$nodo['ajax'].'" data-categoryNews="'.$nodo['categoryNews'].'" data-subcategory="'.$nodo['subcategory'].'" data-typology="'.$nodo['typology'].'" data-limitNews="'.$nodo['limitNews'].'" data-affiliation="'.$nodo['affiliation'].'"  data-trademark="'.$nodo['trademark'].'"  data-dbName="'.$dbName.'">'.$nome[0].'</a>'.$assignView.$viewCores.' - '.$viewAjax.$varAttrAjaxView.$viewCategoryNews.$viewSubcategory.$viewTypology.$viewLimitNews.$viewAffiliation.$viewTrademark;
		
		if(count($nodo->tpl) > 0) {
			$str .= '<ul>';
		}
		foreach($nodo->tpl as $figlio){
			$str .= getLi($figlio);
		}
		if(count($nodo->tpl) > 0) {
			$str .= '</ul>';
		}
		$str .= '</li>';
		return $str;
	}
	
	
//	function getLi( $nodo ){
//		$nome = explode("|",$nodo['name']);
//		$str = '<li id="'.$nodo['name'].'" noRename="true">';
//		$str .= '<a href="#">'.$nome[0].'</a>';
//		foreach($nodo->tpl as $figlio){
//			$str .= '<ul>';
//			$str .= getLi($figlio);
//			$str .= '</ul>';
//		}
//		$str .= '</li>';
//		return $str;
//	}
?>