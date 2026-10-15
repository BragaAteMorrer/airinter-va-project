jQuery(function($) {
    
    
    
    function add_event_click_for_close_rubr_item()
    {
    	//Close Popups and Fade Layer
    	$('p.rubr').on('click', function() {
            var rubrId = $(this).data('rubrid');
            $('#' + rubrId).toggle();
            $('#' + rubrId + 'p').toggle();
            $('#' + rubrId + 'm').toggle();
    	});        
    }
    
    
    
    
    
	// --------------------------------------------------------------------

    /**
     * fonctions à exécuter lors du chargement du script
     *
     * @return void
     */
    function init()
	{
        add_event_click_for_close_rubr_item();
    }
    //
    
    init(); // initialisation  
});