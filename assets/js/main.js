// jQuery(function(){
//     alert('.field_'+ jQuery("#fieldrow-mode select").val())
//     jQuery('.field_'+ jQuery("#fieldrow-mode select").val()).show();
//     jQuery("#fieldrow-mode select").on("change", function(){
//         alert(jQuery("#fieldrow-mode select").val());
//     });
// });
(function() {
    var adminpage = '';
    if (typeof window.adminpage !== 'undefined') {
        adminpage = window.adminpage;
    }
    var timesIntervals = new Array();
    var timeReload;

    function compressImage(event) {
        var element = jQuery(event.target);
        var container = element.closest('td');

        element.attr('disabled', 'disabled');
        element.next().show();
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action: 'iloveimg_compress_library',
              id: element.data('id') || element.attr('data-id')
            },
            success: function(data) {
              element.removeAttr('disabled');
              container.html(data);
            },
            error: function() {
              element.removeAttr('disabled');
              //container.find('span.spinner').addClass('hidden');
            }
          });
    }

    function statusCompressing(element, index){
        var element = jQuery(element);
        var container = element.closest('td');
        
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action: 'iloveimg_compress_library_is_compressed',
              id: element.data('id') || element.attr('data-id')
            },
            success: function(data) {
                
                clearInterval(timesIntervals["ref_" + index]);
                container.html(data);
            },
            error: function() {

            }
          });
    }
    switch (adminpage) {
        case 'upload-php':
        case 'media_page_iloveimg-media-page':
        case 'post-php':
            jQuery(document).on("click", "button.iloveimg-compress", compressImage);
            jQuery(document).on("click", "button#iloveimg_allcompress", function(event){
                jQuery("button#iloveimg_allcompress").attr('disabled', 'disabled');
                jQuery("button.iloveimg-compress").each(function(index, element){
                    var buttonCompress = jQuery(element);
                    buttonCompress.trigger("click");
                    timeReload = setInterval(function(){
                        if(!jQuery("button.iloveimg-compress").length){
                            clearInterval(timeReload);
                            location.reload();
                        }
                    }, 1000)
                });
            });
            jQuery('<option>').val('iloveimg_bulk_action').text("Compress Images").appendTo('select[name=action]');
            jQuery('<option>').val('iloveimg_bulk_action').text("Compress Images").appendTo('select[name=action2]');
            jQuery('.iloveimg_compressing').each(function(index, element) {
                timesIntervals["ref_" + index] = setInterval(function(){
                    statusCompressing(element, index);
                },  1000);
            });

            jQuery(document).on("submit", "form#images-filter, form#posts-filter", function(event){
                if(jQuery(document).find("select#bulk-action-selector-top option:checked").val() == 'iloveimg_bulk_action'){
                    event.preventDefault();
                    jQuery("table.wp-list-table.images tbody tr, table.wp-list-table.media tbody tr").each(function(index, element){
                        if(jQuery(element).find("th.check-column input[type='checkbox']").is(':checked')){
                            jQuery(element).find("td.status button, td.iloveimg_compression button").trigger("click");
                        }
                    });
                }
            });
            break;
    }
}).call();