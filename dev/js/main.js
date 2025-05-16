(function () {
    var adminpage = '';
    if (typeof window.adminpage !== 'undefined') {
        adminpage = window.adminpage;
    }
    var timesIntervals = new Array();
    var timeReload;

    function compressImage(event) {
        var element   = jQuery( event.target );
        var container = element.closest( 'td' );

        element.attr( 'disabled', 'disabled' );
        element.next().show();
        jQuery.ajax(
            {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ilove_img_compress_library',
					id: element.data( 'id' ) || element.attr( 'data-id' ),
					imgnonce: element.data( 'imgnonce' ) || element.attr( 'data-imgnonce' )
				},
				success: function (data) {
					element.removeAttr( 'disabled' );
					container.html( data );
				},
				error: function () {
					element.removeAttr( 'disabled' );
					// container.find('span.spinner').addClass('hidden');
				}
            }
        );
    }

    function statusCompressing(element, index){
        var element   = jQuery( element );
        var container = element.closest( 'td' );

        jQuery.ajax(
            {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ilove_img_compress_library_is_compressed',
					id: element.data( 'id' ) || element.attr( 'data-id' ),
                    imgnonce: element.data( 'imgnonce' ) || element.attr( 'data-imgnonce' )
				},
				success: function (data) {

					clearInterval( timesIntervals["ref_" + index] );
					container.html( data );
				},
				error: function () {

				}
            }
        );
    }
    var totalImagesToCompress = 0;
    switch (adminpage) {
        case 'upload-php':
            jQuery( document ).on( "click", "button.iloveimg-compress", compressImage );
            break;
        case 'media_page_iloveimg-media-page':
        case 'post-php':
            jQuery( document ).on( "click", "button.iloveimg-compress", compressImage );
            jQuery( document ).on(
                "click",
                "button#iloveimg_allcompress",
                function (event) {
					totalImagesToCompress = jQuery( "button.iloveimg-compress" ).length;

					jQuery( "button#iloveimg_allcompress" ).attr( 'disabled', 'disabled' );
					jQuery( "button.iloveimg-compress" ).each(
                        function (index, element) {
                            var buttonCompress = jQuery( element );
                            buttonCompress.trigger( "click" );
                            timeReload = setInterval(
                                function () {
                                    var _percent = ( 100 - (jQuery( "button.iloveimg-compress" ).length * 100) / totalImagesToCompress);
                                    jQuery( "button#iloveimg_allcompress .iloveimg-compress-all__percent" ).width( _percent + "%" );
                                    if ( ! jQuery( "button.iloveimg-compress" ).length) {
                                        clearInterval( timeReload );
                                    }
                                },
                                300
                            );
                            location.reload();
                        }
					);
				}
            );
            jQuery( '<option>' ).val( 'iloveimg_bulk_action' ).text( "Compress Images" ).appendTo( 'select[name=action]' );
            jQuery( '<option>' ).val( 'iloveimg_bulk_action' ).text( "Compress Images" ).appendTo( 'select[name=action2]' );
            jQuery( '.iloveimg_compressing' ).each(
                function (index, element) {
					timesIntervals["ref_" + index] = setInterval(
                        function () {
                            statusCompressing( element, index );
                        },
                        1000
					);
				}
            );

            jQuery( document ).on(
                "submit",
                "form#images-filter, form#posts-filter",
                function (event) {
					if (jQuery( document ).find( "select#bulk-action-selector-top option:checked" ).val() == 'iloveimg_bulk_action') {
						event.preventDefault();
						jQuery( "table.wp-list-table.images tbody tr, table.wp-list-table.media tbody tr" ).each(
                            function (index, element) {
                                if (jQuery( element ).find( "th.check-column input[type='checkbox']" ).is( ':checked' )) {
                                    jQuery( element ).find( "td.status button, td.iloveimg_compression button" ).trigger( "click" );
                                }
                            }
                        );
					}
				}
            );
            break;
    }
    jQuery( ".iloveimg_settings__options-container form input" ).on(
        "change",
        function (element) {
			if ( ! jQuery( ".iloveimg_settings__options-container form .submit button" ).hasClass( 'need_saving' )) {
				setTimeout(
                    function () {
                        jQuery( ".iloveimg_settings__options-container form .submit button" ).addClass( 'need_saving' );
                        setTimeout(
                            function () {
                                jQuery( ".iloveimg_settings__options-container form .submit button" ).removeClass( 'need_saving' );
                            },
                            5000
                        );
                    },
                    1000
                );
			}
		}
    );

    jQuery( ".iloveimg_page_iloveimg-compress-admin-page #iloveimg_restore_all" ).on(
        'click',
        function (event) {
            event.preventDefault();

			let element = jQuery( event.currentTarget );

            const dialogComponent = `
                    <dialog id="iloveimg-compress-restore-dialog" class="iloveimg-restore-dialog">
                        <h2 class="iloveimg-title-dialog">Attention!</h2>
                        <p class="iloveimg-content-dialog">The changes applied by all the tools will be lost. Do you want to continue?</p>
                        <div class="iloveimg-btn-groups">
                            <button id="iloveimg-compress-dialog-aceptted" class="button button-primary">Yes</button>
                            <button id="iloveimg-compress-dialog-close" class="button button-secondary">Close</button>
                        </div>
                    </dialog>`;

            element.parent().append(dialogComponent)

            const dialogElem = document.getElementById("iloveimg-compress-restore-dialog")
            const btnConfirmDialog = document.getElementById("iloveimg-compress-dialog-aceptted")
            const btnCloseDialog = document.getElementById("iloveimg-compress-dialog-close")

            dialogElem.showModal();
            btnConfirmDialog.addEventListener("click", (e) => {
                e.preventDefault();
                element.attr( 'disabled', 'disabled' );
                dialogElem.close();
                dialogElem.remove();

                jQuery.ajax(
                    {
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'ilove_img_compress_restore_all'
                        },
                        success: function () {
                            element.removeAttr( 'disabled' );
                            location.reload();
                        },
                        error: function () {
                            element.removeAttr( 'disabled' );
                        }
                    }
                );
            })

            btnCloseDialog.addEventListener("click", (e) => {
                e.preventDefault();
                dialogElem.close();
                dialogElem.remove();
            })
		}
    );

    jQuery( ".iloveimg_page_iloveimg-compress-admin-page #iloveimg_clear_backup" ).on(
        'click',
        function (event) {
            event.preventDefault();

            let element = jQuery( event.currentTarget );

            const dialogComponent = `
                    <dialog id="iloveimg-compress-restore-dialog" class="iloveimg-restore-dialog">
                        <h2 class="iloveimg-title-dialog">Attention!</h2>
                        <p class="iloveimg-content-dialog">All files inside iloveimg-backup folder will be deleted. Do you want to continue?</p>
                        <div class="iloveimg-btn-groups">
                            <button id="iloveimg-compress-dialog-aceptted" class="button button-primary">Yes</button>
                            <button id="iloveimg-compress-dialog-close" class="button button-secondary">Close</button>
                        </div>
                    </dialog>`;

            element.parent().append(dialogComponent)

            const dialogElem = document.getElementById("iloveimg-compress-restore-dialog")
            const btnConfirmDialog = document.getElementById("iloveimg-compress-dialog-aceptted")
            const btnCloseDialog = document.getElementById("iloveimg-compress-dialog-close")

            dialogElem.showModal();
            btnConfirmDialog.addEventListener("click", (e) => {
                e.preventDefault();
                element.attr( 'disabled', 'disabled' );
                dialogElem.close();
                dialogElem.remove();

                jQuery.ajax(
                    {
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'ilove_img_compress_clear_backup'
                        },
                        success: function () {
                            element.removeAttr( 'disabled' );
                            location.reload();
                        },
                        error: function () {
                            element.removeAttr( 'disabled' );
                        }
                    }
                );
            })

            btnCloseDialog.addEventListener("click", (e) => {
                e.preventDefault();
                dialogElem.close();
                dialogElem.remove();
            })
		}
    );

    jQuery( ".iloveimg-compress.iloveimg_restore_button_wrapper .iloveimg_restore_button" ).on(
        'click',
        function (event) {
            event.preventDefault();

            let element = jQuery( event.currentTarget );
            let fieldNonce = jQuery( event.currentTarget ).siblings( "#_wpnonce" );
            fieldNonce = fieldNonce.val();
            let action = element.data('action');
            let imageId = element.data('id');

			element.hide();
            element.nextAll('.loading').show();

            const dialogComponent = `
                    <dialog id="iloveimg-compress-restore-dialog" class="iloveimg-restore-dialog">
                        <h2 class="iloveimg-title-dialog">Attention!</h2>
                        <p class="iloveimg-content-dialog">The changes applied by all the tools will be lost. Do you want to continue?</p>
                        <div class="iloveimg-btn-groups">
                            <button id="iloveimg-compress-dialog-aceptted" class="button button-primary">Yes</button>
                            <button id="iloveimg-compress-dialog-close" class="button button-secondary">Close</button>
                        </div>
                    </dialog>`;
            
            element.parent().append(dialogComponent)

            const dialogElem = document.getElementById("iloveimg-compress-restore-dialog")
            const btnConfirmDialog = document.getElementById("iloveimg-compress-dialog-aceptted")
            const btnCloseDialog = document.getElementById("iloveimg-compress-dialog-close")

            dialogElem.showModal();
            btnConfirmDialog.addEventListener("click", (e) => {
                e.preventDefault();
                element.attr( 'disabled', 'disabled' );
                dialogElem.close();
                dialogElem.remove();

                jQuery.ajax(
                    {
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            'action': action,
                            'id': imageId,
                            '_wpnonce': fieldNonce
                        },
                        dataType: 'json',
                        success: function (data) {
                            element.nextAll('.loading').hide();
                            element.nextAll('.success').html( data.data ).show();
                            location.reload();
                        },
                        error: function (error) {
                            element.nextAll('.loading').hide();
                            element.nextAll('.error').html( error.responseJSON.data ).show();
                            element.show();
                        }
                    }
                );
            })

            btnCloseDialog.addEventListener("click", (e) => {
                e.preventDefault();
                element.show();
                element.nextAll('.loading').hide();
                dialogElem.close();
                dialogElem.remove();
            })
		}
    );

}).call();