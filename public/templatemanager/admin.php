<?php
	session_start();
	$sessioneId = session_id();
	ini_set('display_errors', 1 ); 
	
	global $myPath,$isGoAction;
	$pathSite = getenv( 'pathSite' );
	global $myPath,$default,$sitoTemplate,$isGoAction;
	$pathSite = getenv( 'pathSite' );
    
    
    exit;
    $user = 'root';
    $pass = 'mysql2018houseop';
    $db = new PDO('mysql:host=localhost;', $user, $pass);
    
    $cartellaLavoro = !empty( $_REQUEST['folder'] ) ? $_REQUEST['folder'].'/' : 'livescore24/';
    if( empty( $_REQUEST['dbName'] ) ) {
        die('Inserisci il nome del db delle categorie');
    }
    $dbName = $_REQUEST['dbName'];
   
    
    $sth = $db->prepare("select * from $dbName.categories order by name");
    $sth->execute();
    $aCategories = $sth->fetchAll( PDO::FETCH_OBJ );
    
    $sth = $db->prepare("select * from $dbName.subcategories order by name");
    $sth->execute();
    $aSubcategories = $sth->fetchAll( PDO::FETCH_OBJ );
    
    
    $sth = $db->prepare("select * from $dbName.typologies order by name");
    $sth->execute();
    $aTypologies = $sth->fetchAll( PDO::FETCH_OBJ );
    
    $sth = $db->prepare("select idAffiliation,name from $dbName.affiliation order by name");
    $sth->execute();
    $aAffiliations = $sth->fetchAll( PDO::FETCH_OBJ );
    
	$siteObjectsElements  = new stdClass();
    $siteObjectsElements->config = new stdClass();
    $siteObjectsElements->config->cartellaXmlTemplate       = __DIR__.'/../../app/Resources/layouts/'.$cartellaLavoro;
    $siteObjectsElements->config->cartellaLetturaTemplate   = __DIR__.'/../../app/Resources/views/'.$cartellaLavoro;
    $siteObjectsElements->config->pathSviluppoTemplate      = __DIR__.'/../../app/Resources/views/'.$cartellaLavoro;
    $siteObjectsElements->config->pathSviluppoCores         = __DIR__.'/../../src/AppBundle/Service/WidgetService';
    
    $cores = includeFolder( $siteObjectsElements->config->pathSviluppoCores );
    $cores = array_flip( $cores );
    unset( $cores['WidgetManager.php'] );
    $cores = array_flip($cores);
    

    echo '<div id="popupFunzioni" style="display: block;">
            <div id="close">[X]</div>
            <div class="boxtitle">Pannello controllo template:<br> <span>"'.$_REQUEST['template'].'"</span></div>
            
            <div class="item">
                <div class="label">
                    Assegna contenuto a variabile:
                </div>
                <div class="value">
                    <input type="text" id="varTpl" value="'.$_REQUEST['varTpl'].'">
                </div>
            </div>    
            
            <div class="item">
                <div class="label">
                    Metodo implementazione:
                </div>
                <div class="value">
                     <select id="ajax">';
                        $selectedSinc = $_REQUEST['ajax'] == '0' ? 'selected' : '';  
                        $selectedAsincOnLoad = $_REQUEST['ajax'] == '1' ? 'selected' : '';  
                        $selectedAsincOnEvent = $_REQUEST['ajax'] == '2' ? 'selected' : '';  
                        echo'
                        <option value="0" '.$selectedSinc.'>Sincrono</option>
                        <option value="1" '.$selectedAsincOnLoad.'>Asincrono ( ON LOAD PAGE )</option>
                        <option value="2" '.$selectedAsincOnEvent.'>Asincrono ( ON EVENT )</option>
                    </select>
                </div>
            </div>    
            
            <div class="item">
                <div class="label">
                    Attributo replace chiamata Ajax:
                </div>
                <div class="value">
                    <input type="text" id="varAttrAjax" value="'.$_REQUEST['varAttrAjax'].'">
                </div>
            </div>  
            
            <div class="item">
                <div class="label">
                    Categoria:
                </div>
                <div class="value">
                     <select id="categoryNews">';
                        $selectedSinc = $_REQUEST['categoryNews'] == 0 ? 'selected' : '';
                        $selectedAllNews = $_REQUEST['categoryNews'] == 'allnews' ? 'selected' : '';
                        echo '<option value="0" '.$selectedSinc.'>Nessuna</option>';  
                        echo '<option value="allnews" '.$selectedAllNews.'>All News</option>';  
                        
                        foreach( $aCategories AS $category ) {                            
                            $selectedSinc = $_REQUEST['categoryNews'] == $category->id ? 'selected' : '';
                            echo '<option value="'.$category->id.'" '.$selectedSinc.'>'. html_entity_decode( $category->name ).'</option>';
                        }
                        echo'
                    </select>
                </div>
            </div>    
            
              
            
             <div class="item">
                <div class="label">
                    Numero News:
                </div>
                <div class="value">
                     <select id="limitNews">';
                        echo '<option value="0" '.$selectedSinc.'>Tutte</option>';     
                        for( $x = 1; $x <= 30; $x++ ) {
                            $selectedSinc = $_REQUEST['limitNews'] == $x ? 'selected' : '';                                                   
                            echo '<option value="'.$x.'" '.$selectedSinc.'>'.$x.'</option>';
                        }
                        echo'
                    </select>
                </div>
            </div> 
            <div class="item">
                <div class="label">
                    Sottocategoria:
                </div>
                <div class="value">
                     <select id="subcategory">';
                        $selectedSinc = $_REQUEST['subcategory'] == 0 ? 'selected' : '';
                        $selectedAllNews = $_REQUEST['subcategory'] == 'all' ? 'selected' : '';
                        echo '<option value="0" '.$selectedSinc.'>Nessuna</option>';  
                        echo '<option value="allnews" '.$selectedAllNews.'>All News</option>';  
                        
                        foreach( $aSubcategories AS $subcategory ) {                            
                            $selectedSinc = $_REQUEST['subcategory'] == $subcategory->id ? 'selected' : '';
                            echo '<option value="'.$subcategory->id.'" '.$selectedSinc.'>'. html_entity_decode( $subcategory->name ).'</option>';
                        }
                        echo'
                    </select>
                </div>
            </div>
            <div class="item">
                <div class="label">
                    Tipologia:
                </div>
                <div class="value">
                     <select id="typology">';
                        $selectedSinc = $_REQUEST['typology'] == 0 ? 'selected' : '';
                        $selectedAllNews = $_REQUEST['typology'] == 'all' ? 'selected' : '';
                        echo '<option value="0" '.$selectedSinc.'>Nessuna</option>';  
                        
                        foreach( $aTypologies AS $typology ) {                            
                            $selectedSinc = $_REQUEST['typology'] == $typology->id ? 'selected' : '';
                            echo '<option value="'.$typology->id.'" '.$selectedSinc.'>'. html_entity_decode( $typology->name ).'</option>';
                        }
                        echo'
                    </select>
                </div>
            </div>
            <div class="item">
                <div class="label">
                    Affiliato:
                </div>
                <div class="value">
                     <select id="affiliation">';
                        echo '<option value="" '.$selectedSinc.'>Nessuno</option>';     
                        foreach( $aAffiliations as $affiliation ) {
                            $selectedAffiliations = $_REQUEST['affiliation'] == $affiliation->idAffiliation ? 'selected' : '';                                                   
                            echo '<option value="'.$affiliation->idAffiliation.'" '.$selectedAffiliations.'>'.$affiliation->name.'</option>';
                        }
                        echo'
                    </select>
                </div>
            </div> 
              
            <div class="item">
                <div class="label">
                    Marchio:
                </div>
                <div class="value">
                    <input type="text" id="trademark" value="'.$_REQUEST['trademark'].'">
                </div>
            </div> 
            
            

            <div class="item">
                <div class="label">
                    CORE template:
                </div>
                <div class="value">
                    <select multiple id="cores">';
                        foreach( $cores AS $core ) {
                            $core = str_replace( '.php', '', $core );
                            $aCores = explode( ' ', $_REQUEST['cores'] );
                            $selected = in_array( $core, $aCores ) ? 'selected' : '';
                            echo '<option value="'.$core.'" '.$selected.'>'.$core.'</option>';
                        }
                echo '
                    </select>
                </div>
            </div>                
            
            <div class="clear">
                <input type="button" id="btnSetConfig" value="Invia">
            </div>
        </div>
    ';
    
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
		sort( $arrayfile );
		return $arrayfile;
	}
