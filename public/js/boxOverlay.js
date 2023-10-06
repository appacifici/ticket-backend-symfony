
/**
 * Classe per la gestione dei commenti
 */
var BoxOverlay = function () {
    var that = this;
    this.iCurrentItemGallery = 0;
    this.initListeners();
    this.lastOpen = 0;
};


/**
 * Metodo che avvia gli ascoltatori
 */
BoxOverlay.prototype.initListeners = function () {
    var that = this;       
    $( 'body' ).on( 'click', '[data-openOverlay]', function( event ) {
        event.stopPropagation();
        that.getData( this );        
    }); 
    $( 'body' ).on( 'click', '[data-imageGallery] [data-galleryBtn]', function() {
        that.moveGallery( this );
    }); 
    
};

/**
 * Metodo che crea una galleria scorrevole di immagini
 * @param {type} sender
 * @returns {undefined}
 */
BoxOverlay.prototype.moveGallery = function( sender ) {
    var direction = $( sender ).attr( 'data-galleryBtn' );    
    
    if( direction == '+' ) {
        this.iCurrentItemGallery++;
    } else {
        this.iCurrentItemGallery--;
    }   
    
    $( '[data-imgItem]' ).each(function( index ) {
        $( this ).hide();
    });
    
    $( '[data-imgItem="'+this.iCurrentItemGallery+'"]' ).show();
    $( '[data-currentImg]' ).html( this.iCurrentItemGallery +1 );
};

/**
 * Recupera i dati dal db e crea l'html per il popup di risposta
 * @param object sender
 * @returns {void}
 */
BoxOverlay.prototype.getData = function( sender ) {
    var entity  = $( sender ).attr( 'data-openOverlay' );
    var id      = $( sender ).attr( 'data-id' );
    
    var request = $.ajax ({
        url: "/getDataBoxOverlayByEntity/"+entity+"/"+id,
        type: "POST",
        async: true,
        dataType: "html"        
    });
    request.done( function( resp ) {
         var params = { 
            type: 'custom', 
            title: '' ,
            callbackModals: resp
            };
        classModals.openModals( params );
       
    });
};

var boxOverlay   = null;
boxOverlay       = new BoxOverlay();