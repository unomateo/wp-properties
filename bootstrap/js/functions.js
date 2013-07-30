var file_frame;

  jQuery('#btn_update').live('click', function( event ){
      var data = { id: '<?php echo $id ?>', action: 'update_description', description: jQuery('#description').val() };
      jQuery.post(ajaxurl, data, function(response) {
         alert(response);
        });
  });

  // sets the style to the description field so the user knows they are editing
  jQuery('#description').focus(function(){
    jQuery(this).css('color', '#cc0000');
  });
 
  // user wants to upload images
  jQuery('.upload_image_button').live('click', function( event ){
 
    event.preventDefault();
    var is_multiple = jQuery(this).data('multiple'); 
    var property_id =jQuery('.property_container').data('id');
 
    // If the media frame already exists, reopen it.
    if ( file_frame ) {
      //file_frame.open();
      //return;
    }
 
    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
      title: jQuery( this ).data( 'uploader_title' ),
      button: {
        text: jQuery( this ).data( 'uploader_button_text' ),
      },
      multiple: is_multiple  // Set to true to allow multiple files to be selected
    });

    file_frame.open();
 
    // When an image is selected, run a callback.
    file_frame.on( 'select', function() {
      
       var selection = file_frame.state().get('selection');

       if(!is_multiple){
          attachment = selection.first().toJSON();
          set_main_image(attachment.url, property_id);
       } else {
          var images = [];
          selection.map( function( attachment ) {
            attachment = attachment.toJSON();
            images.push(attachment.url);
          }); // end map
          set_images(images, property_id);
       }

    });

    function set_images(images, property_id){
      var data = { id:property_id , images:images, action: 'insert_property_image', data:data };
      //console.log(data);
        jQuery.post(ajaxurl, data, function(response) {
          console.log(response);
          jQuery.each(response, function(i, item){
            jQuery("#property_image_ul").append("<li><a href='#' class='thumbnail'><img style='height:80px' src='"+item+"' alt=''></a></li>"); 
          })
        });
    }

    function set_main_image(property_main_image, property_id){

      var data = { id:property_id , image:property_main_image, action: 'update_main_property_image', data:data };
      jQuery.post(ajaxurl, data, function(response) {
       
        jQuery("#main_image").attr('src', property_main_image);     
          
      });
    }
 
    // Finally, open the modal
    file_frame.open();
  });