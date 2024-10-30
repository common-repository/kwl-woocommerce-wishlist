jQuery(document).ready(function($) {

    //console.log(document.cookie);

    $('body').append('<div id="kwl-dialog-add" class="kwl-hideme">Product added to Product List</div>');
    $('body').append('<div id="kwl-dialog-remove" class="kwl-hideme">Product removed from Product List</div>');

    $('.kwl-add').click(function(){
        var pid = $(this).data('product_id');
        pid = parseInt(pid);
        var data = {
            action: 'kwl_add',
            product_id: pid
        };

        var refr = $(this);

        $.post(ajax_object.ajax_url, data, function(response) {
            $('#kwl-dialog-add').dialog({
                  resizable: true,
                  dialogClass: "kwl-close",
                  closeOnEscape: true,
                  height: "auto",
                  width: 400,
                  modal: true,
                  buttons: {
                    Close: function() {
                      $( this ).dialog( "close" );
                    }
                  }
                });
            refr.text("Added");
        });
    });

    $('.kwl-remove').click(function(){
        var pid = $(this).data('product_id');
        pid = parseInt(pid);
        var data = {
            action: 'kwl_remove',
            product_id: pid
        };

        $.post(ajax_object.ajax_url, data, function(response) {
            $('#kwl-dialog-remove').dialog({
                  resizable: true,
                  dialogClass: "kwl-close",
                  closeOnEscape: true,
                  height: "auto",
                  width: 400,
                  modal: true,
                  buttons: {
                    Close: function() {
                      $( this ).dialog( "close" );
                      location.reload();
                      //$("#kwl-list-holder").load(" #kwl-list-holder");
                    }
                  }
                });
            
        });
    });



});