var templatemanager = null;
jq( document ).ready( function() {
    var templatemanager = new Templatemanager();
});


/**
 * Metodo costruttore
 */
var Templatemanager = function() {
    var that = this;
    this.conteinerStructureXml = jq( '#dhtmlgoodies_tree2 li ul' );
    this.popup = jq( '#popupFunzioni' );
    this.initListeners();
    //alert( 'si' );
}

/**
 * Metodo che avvia gli ascoltatori 
 */
Templatemanager.prototype.initListeners = function() {
    this.setObserveElement( 'a' );
}

/**
 * Metodo che avvia gli ascoltatori sull elemento passto come argomento alla funzione
 * @params string element => nome dell'elemento su cui avviare gli ascoltatori sugli eventi
 */
Templatemanager.prototype.setObserveElement = function( element ) {
    var that = this;
    switch( element ) {
        case 'a':
            this.tpl = this.conteinerStructureXml.find( 'a' );
            this.tpl.on( 'dblclick', function( e ){
               that.openPopup( this );
            });
        break;
    }
}

/**
 * Metodo che apre il popup per inserire nel attributo delle elemento del tpl il nome della
 * varibile smarty a cui associare il suo contenuto
 * @param {object} element
 * @returns {void}
 */
Templatemanager.prototype.openPopup = function( element ) {
    var that = this
    
    this.popup.empty();  
        
    var defaultValue = typeof jq( element ).attr( 'data-varTpl') != 'undefined' ? jq( element ).attr( 'data-varTpl') : jq( element ).closest( 'ul' ).closest( 'li' ).find( 'a' ).text();
    var defaultAttrAjax = typeof jq( element ).attr( 'data-varAttrAjax') != 'undefined' ? jq( element ).attr( 'data-varAttrAjax') : jq( element ).closest( 'ul' ).closest( 'li' ).find( 'a' ).text();
    var defaultCores = typeof jq( element ).attr( 'data-cores') != 'undefined' ? jq( element ).attr( 'data-cores') : '';
    var defaultAjax = typeof jq( element ).attr( 'data-ajax') != 'undefined' ? jq( element ).attr( 'data-ajax') : 0;
    var defaultCategoryNews = typeof jq( element ).attr( 'data-categoryNews') != 'undefined' ? jq( element ).attr( 'data-categoryNews') : 0;
    var defaultSubcategory = typeof jq( element ).attr( 'data-subcategory') != 'undefined' ? jq( element ).attr( 'data-subcategory') : 0;
    var defaultTypology = typeof jq( element ).attr( 'data-typology') != 'undefined' ? jq( element ).attr( 'data-typology') : 0;
    var defaultLimitNews = typeof jq( element ).attr( 'data-limitNews') != 'undefined' ? jq( element ).attr( 'data-limitNews') : 0;
    var dbName = typeof jq( element ).attr( 'data-dbName') != 'undefined' ? jq( element ).attr( 'data-dbName') : '';
    
    var affiliation = typeof jq( element ).attr( 'data-affiliation') != 'undefined' ? jq( element ).attr( 'data-affiliation') : '';
    var trademark = typeof jq( element ).attr( 'data-trademark') != 'undefined' ? jq( element ).attr( 'data-trademark') : '';
    
    var template = jq( element ).closest( 'li' ).attr('id').split( '|' );
    
    var request = jq.ajax({
        url: "getform.php",
        type: "POST",
        data: {
            'dbName': dbName,
            'varAttrAjax': defaultAttrAjax,
            'varTpl': defaultValue,
            'template': template[0],
            'cores': defaultCores,
            'ajax': defaultAjax,
            'categoryNews': defaultCategoryNews,
            'subcategory': defaultSubcategory,
            'typology': defaultTypology,
            'limitNews': defaultLimitNews,
            'affiliation': affiliation,
            'trademark': trademark            
        },
        dataType: "html"
    }).done( function( response ) {
        that.popup.html( response );
        jq( '#close' ).click( function() {
            that.popup.fadeOut();
        });
        jq( '#btnSetConfig' ).click( function() {
            jq( element ).attr( 'data-varTpl', that.popup.find( '#varTpl' ).val() );
            jq( element ).attr( 'data-varAttrAjax', that.popup.find( '#varAttrAjax' ).val() );
            var cores = '';
            jq("#cores :selected").map(function(i, el) {
                cores += jq(el).val()+' ';                
            });            
            jq( element ).attr( 'data-cores', cores.slice( 0, -1 ) );            
            if( cores.slice( 0, -1 ) != '' )
                jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_coresInfo' ).html( 'Core: '+cores.slice( 0, -1 ) );           
            
            if( that.popup.find( '#varTpl' ).val() != '' )
                jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_assignInfo' ).html( 'Assign: '+that.popup.find( '#varTpl' ).val() );
            
            if( that.popup.find( '#varAttrAjax' ).val() != '' )
                jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_varAttrAjaxInfo' ).html( 'varAttrAjax: '+that.popup.find( '#varAttrAjax' ).val() );
            
            if( that.popup.find( '#categoryNews' ).val() != '' )
                jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_categoryNewsInfo' ).html( 'Categoria: '+that.popup.find( '#categoryNews' ).val() );
                        
            if( that.popup.find( '#subcategory' ).val() != '' )
                jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_subcategoryInfo' ).html( 'Sottocategoria: '+that.popup.find( '#subcategory' ).val() );
                                                
            if( that.popup.find( '#typology' ).val() != '' )
                jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_typologyInfo' ).html( 'Tipologia: '+that.popup.find( '#typology' ).val() );
                        
            if( that.popup.find( '#limitNews' ).val() != '' )
                jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_limitNewsInfo' ).html( 'Limit: '+that.popup.find( '#limitNews' ).val() );
            
            
            
            jq( element ).attr( 'data-trademark', that.popup.find( '#trademark' ).val() );    
             
            jq( element ).attr( 'data-ajax', jq( '#ajax :selected').val() );            
            jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_ajaxInfo' ).html( 'Modalità: '+ jq( '#ajax :selected').html() );           
            
            jq( element ).attr( 'data-categoryNews', jq( '#categoryNews :selected').val() );            
            jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_categoryNews' ).html( 'Categoria: '+ jq( '#categoryNews :selected').html() );           
            
            jq( element ).attr( 'data-subcategory', jq( '#subcategory :selected').val() );            
            jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_subcategory' ).html( 'Sottocategoria: '+ jq( '#subcategory :selected').html() );           
                        
            jq( element ).attr( 'data-typology', jq( '#typology :selected').val() );            
            jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_typology' ).html( 'Tipologia: '+ jq( '#typology :selected').html() );           
            
            jq( element ).attr( 'data-limitNews', jq( '#limitNews :selected').val() );            
            jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_limitNews' ).html( 'Limit: '+ jq( '#limitNews :selected').html() );           
            
            jq( element ).attr( 'data-affiliation', jq( '#affiliation :selected').val() );            
            jq( '#'+jq( element ).closest( 'li' ).attr('id').replace('|','')+'_affiliation' ).html( 'Limit: '+ jq( '#affiliation :selected').html() );           
            
            that.popup.fadeOut();
        });
    });
    
    

//    this.sendButton = jq( '<input>' ).attr({ type: 'button', id: 'inviaVar', value: 'Invia' }).appendTo( this.popup );
//    
    this.popup.fadeIn();
};


//var gestoreSmarty = null;
//
//Event.observe(window,"load",function() {
//	gestoreSmarty = new GestoreSmarty;
//});
//
//var GestoreSmarty = Class.create({
//	
//	initialize: function() {
//		this.dhtmlgoodies_tree2 = $_('dhtmlgoodies_tree2').select('li');
//		this.popupFunzioni = $_('popupFunzioni');
//		this.funzioni = $_('popupFunzioni').select('li');
//		this.addF = null;
//		this.closeF = null;
//		this.scelta;
//		this.sender = null;
//		this.countLi = 0;
//		this.initListeners();		
//	},
//	
//	
//	initListeners: function() {
//		var that = this;	
//		if (null != this.dhtmlgoodies_tree2) {			
//			for (x=0; x < this.dhtmlgoodies_tree2.length; x++) {
//				this.dhtmlgoodies_tree2[x].observe('dblclick', function(){that.apriPopupFunzioni(this);});
//			}
//		} 
//	},
//	
//	apriPopupFunzioni: function(sender) {
//		var that = this;
//		this.countLi
//		if ( this.countLi == 0 ) {
//			this.popupFunzioni.setStyle({"display":"block"});	
//			new Ajax.Request('index.php', {
//				method:  'post',
//				parameters: {'actionForm':'getSelectList','funzioni':sender.readAttribute("name")},
//			  onSuccess: function(response) {
//			 
//			    $_('popupFunzioni').innerHTML = response.responseText;
//			    that.closeF = $_('close');
//					that.closeF.observe('click', function(){that.closeAssegnaFunzione(this);});
//			    that.addF = $_('addF');
//					that.addF.observe('click', function(){that.addFunzioni(this);});
//			  }
//			});
//			this.sender = sender;
//			this.countLi++;
//		} else {
//			this.countLi++;
//		}
//	},
//	
//	closeAssegnaFunzione: function() {
//		this.popupFunzioni.setStyle({"display":"none"});
//		this.countLi = 0;
//	},
//	
//	addFunzioni: function(sender) {
//		var options = $$('select#mySelect option');
//		var len = options.length;
//		var scelte = "";
//		for (var i = 0; i < len; i++) {
//			if(options[i].selected ) {
//		    console.log('Option text = ' + options[i].text);
//		    console.log('Option value = ' + options[i].value);
//		    scelte +=  options[i].value+"-";
//		  }
//		}
//		alert("Funzioni Settate");
//		this.sender.setAttribute('name',scelte);
//		this.closeF.stopObserving();
//		this.addF.stopObserving();
//		this.closeAssegnaFunzione();
//		this.countLi = 0;
//	}
//});
