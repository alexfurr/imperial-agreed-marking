
var ICL_AGREED_MARKING_JS = {

    //---
    wrapper_id:    'agreed_marking_form_wrap',

    //---
    init: function () {
        this.add_listeners();
    },

    //---
    add_listeners: function () {
        jQuery('#' + ICL_AGREED_MARKING_JS.wrapper_id ).on( 'click', '.am-has-click-event', function ( event ) {
            ICL_AGREED_MARKING_JS.on_ui_event( event, this );
            event.preventDefault();
        });

    },

    //---
    on_ui_event: function ( event, element ) {
        var method = jQuery( element ).attr('data-method');
        if ( typeof ICL_AGREED_MARKING_JS[ method ] !== 'undefined' ) {
            ICL_AGREED_MARKING_JS[ method ]( event, element );
        }
    },

    // List of actual interactions
   //---
   submit_agreed_form: function ( event, element ) {

       /// Get all form elements
       // go through each element and recalculate quote price
       var error_count = 0;

       var first_error_id = '';

        jQuery(".agreedMarkingTextarea").each(function() {
            var this_id = this.id;
            var this_val = jQuery(this).val();

            if(first_error_id=="")
            {
                first_error_id = this_id;
            }


            if(jQuery(this).val()=="")
            {
                console.log("test");
                jQuery("#"+this_id).addClass("am-error-textbox");
                error_count++;
            }
        });
        if(error_count==0)
        {
            jQuery("#myform").submit();
        }
        else
        {
            // Scroll to the error
            document.getElementById(first_error_id).scrollIntoView();
            alert("You have not added feedback  in "+error_count+" comment box(es) which are highlighted in red");

        }





   },





};


jQuery( document ).ready( function () {
    ICL_AGREED_MARKING_JS.init();
});
