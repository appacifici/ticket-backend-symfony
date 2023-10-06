<?php
	session_start();
	$sessioneId = session_id();
	ini_set('display_errors', getenv( 'viewErrors' ) ); 
	
	global $myPath,$isGoAction;
	$pathSite = getenv( 'pathSite' );
	global $myPath,$default,$sitoTemplate,$isGoAction;
	$pathSite = getenv( 'pathSite' );
    
    $cartellaLavoro = !empty( $_GET['folder'] ) ? $_GET['folder'].'/' : 'livescore24/';
   
	$siteObjectsElements  = new stdClass();
    $siteObjectsElements->config = new stdClass();
    $siteObjectsElements->config->cartellaXmlTemplate  = __DIR__.'/../../app/Resources/layouts/'.$cartellaLavoro;
    $siteObjectsElements->config->cartellaLetturaTemplate  = __DIR__.'/../../app/Resources/views/'.$cartellaLavoro;
    $siteObjectsElements->config->pathSviluppoTemplate = __DIR__.'/../../app/Resources/views/'.$cartellaLavoro;
  
	$body = "";
	error_reporting(0);
	
	$_POST = $_GET;
	if(!isset($_POST['saveString']))die("no input");
	$items = explode(",",$_POST['saveString']);
    
    //Variabile che serve per cambiare la root path del server in caso siamo dell'admin
    $rootDir = '';
    if ( $_REQUEST['section'] == '/templatemanager/admin.php' ) {
        $rootDir = 'admin/';
    }
    
	$file = $siteObjectsElements->config->cartellaXmlTemplate.$rootDir.$_SESSION["nomeFileStruttura"];
	$arr = array();
    
	for($no=0;$no<count($items);$no++){
		$tokens = explode("-",$items[$no]);
		$arr[] = array('nome' => trim($tokens[0]), 'padre' => trim($tokens[1]));
	}
    
	$root=array('nome'=>'node0', 'figli' => array() );	
    
	function scan( $padre, $arr){
		$figli = array();
		
		foreach( $arr as $v ) {            
//            list( $nome, $idRand, $varTpl ) = explode( '|', $v['nome'] );            
            
            $data = explode( '|', $v['nome'] );
            $nome = $data[0];
            $idRand = $data[1];
            
            $varTpl = 'null';
            $varAttrAjax = 'null';
            $cores = 'null';
            $ajax = 'null';
            $categoryNews = 'null';
            $subcategory = 'null';
            $typology = 'null';
            $limitNews = 'null';
            $affiliation = 'null';
            $trademark = 'null';
            
            for( $x=2; $x < count($data); $x++) {
                if( strpos( ' '.$data[$x], 'varTpl' ) ) {
                    $varTpl = str_replace( 'varTpl=', '', $data[$x] );
                    
                } else if( strpos( ' '.$data[$x], 'varAttrAjax' ) ) {
                    $varAttrAjax = str_replace( 'varAttrAjax=', '', $data[$x] );
                    
                } else if( strpos( ' '.$data[$x], 'cores' ) ) {
                    $cores = str_replace( 'cores=', '', $data[$x] );
                    
                } else if( strpos( ' '.$data[$x], 'ajax' ) ) {
                    $ajax = str_replace( 'ajax=', '', $data[$x] );
                    
                } else if( strpos( ' '.$data[$x], 'categoryNews' ) ) {
                    $categoryNews = str_replace( 'categoryNews=', '', $data[$x] );
                    
                } else if( strpos( ' '.$data[$x], 'subcategory' ) ) {
                    $subcategory = str_replace( 'subcategory=', '', $data[$x] );
                    
                    
                } else if( strpos( ' '.$data[$x], 'typology' ) ) {
                    $typology = str_replace( 'typology=', '', $data[$x] );
                    
                } else if( strpos( ' '.$data[$x], 'limitNews' ) ) {
                    $limitNews = str_replace( 'limitNews=', '', $data[$x] );
                
                } else if( strpos( ' '.$data[$x], 'affiliation' ) ) {
                    $affiliation = str_replace( 'affiliation=', '', $data[$x] );
                
                } else if( strpos( ' '.$data[$x], 'trademark' ) ) {
                    $trademark = str_replace( 'trademark=', '', $data[$x] );
                }
            }
            
            
            $varTpl = $varTpl != 'null' ? $varTpl : '';
            $varAttrAjax = $varAttrAjax != 'null' ? $varAttrAjax : '';
            $affiliation = $affiliation != 'null' ? $affiliation : '';
            $trademark = $trademark != 'null' ? $trademark : '';
            $subcategory = $subcategory != 'null' ? $subcategory : '';
            $typology = $typology != 'null' ? $typology : '';

			if( $v['padre'] == $padre) {
				$body.= "<tpl name=\"".$nome.'|'.$idRand."\" padre=\"".($v['padre'] == 'node0' ? "index|".rand() : $v['padre'] )."\" assign=\"$varTpl\" 
                        varAttrAjax=\"$varAttrAjax\" cores=\"$cores\" ajax=\"$ajax\" limitNews=\"$limitNews\" categoryNews=\"$categoryNews\" subcategory=\"$subcategory\"
                        typology=\"$typology\" affiliation=\"$affiliation\" trademark=\"$trademark\"
                >";
				$body.= scan( $nome.'|'.$idRand, $arr );
				$body.= "</tpl>";
			}
		}
		return $body;
	}
	
	$body.= '<?xml version="1.0" encoding="UTF-8"?>';
	$body.= '<root name="root">';
	$body.= scan('node0', $arr);
	$body.= '</root>';
	
	$f = fopen($file, "w");
	fwrite($f,$body);
	
	if ( fclose( $f ) ) {
		echo "MODIFICHE EFFETTUATE CON SUCCESSO";
        echo "\n\n".shell_exec( "xmllint --format $file" );
    } else {
		echo "ATTENZIONE: Si sono verificati problemi nella modifica del file";
    }