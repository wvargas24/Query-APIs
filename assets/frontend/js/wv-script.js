jQuery(document).ready(function( $ ){

	var ajaxurl = HOUZEZ_ajaxcalls_vars.admin_url+ 'admin-ajax.php';
	var autosearch_text = HOUZEZ_ajaxcalls_vars.autosearch_text;

	function GridPropertiesMLS(status, ord, view, element, page, featured) {
		var n = ord.indexOf(" ");
		var order = ord.substring(n+1, ord.lenght);
		var orderby = ord.substring(0, n);
		var term_id = $('#term_id').data('term_id'); 
		var slug = $('#term_id').data('slug'); 
		console.log('term_id: '+term_id+' slug: '+slug+' Featured: '+featured+' Order: '+order+' Element: '+element);

		console.log('orderby: '+orderby+' status: '+status+' view: '+view+' page: '+page);

		$.post(
	        ajax_object.ajaxurl, {
	        action: 'loadPropertymlsByTabs',
	        status: status,
	        order: order,
	        orderby: orderby,
	        view: view,
	        slug: slug,
	        page: page,
	        featured:featured,
			element:element
	    }, function(data) {	
	        $(element).html(data);
	    });
	}

	/*
     *  Print Property
     * *************************************** */
    if( $('.blokhausre-print-property').length > 0 ) {
        $('.blokhausre-print-property').click(function (e) {
            e.preventDefault();
            var propID, printWindow;

            propID = $(this).attr('data-propid');

            printWindow = window.open('', 'Print Me', 'width=700 ,height=842');

            var address = $('#propertymls_address').text();
            var price = $('#propertymls-price').text();
            var description = $('#propertymls-description').text();

            $.post(
		        ajax_object.ajaxurl, {
		        action: 'blokhausre_print_propertymls',
		        propid: propID,
		        address: address,
		        price: price,
		        description: description
		    }, function(data) {	
		        printWindow.document.write(data);
                printWindow.document.close();
                printWindow.focus();
		    });
        });
    }

	$(document).on('click', '.mls-tabs .link-status', function (e) {
		e.preventDefault();
	    var status = $(this).attr('href');
	    var order = $('.mls-order a.dropdown-item.active').data('order');
	    var view = $('.mls-view .view-btn.active').data('view');
	    var element = "#mlsgrid";
	    var page = 1;
	    var featured = 0;
	    if ( $("#mls-pagination").length ){
	    	page = $('#mls-pagination a.active').attr('href').split('page=')[1];
	    }	    

	    if ( $("#mls-featured").length ){
	    	featured = $("#mls-featured").val();
	    }	    

	    $('.listing-tabs .mls-tabs .nav-link').removeClass('active');
	    $(this).addClass('active');

	    GridPropertiesMLS(status, order, view, element, page, featured);
	    $('.pagination-original').remove();
  	});

  	$(document).on('click', '.mls-order a.dropdown-item', function (e) {
  		e.preventDefault();
	    var orderby = $(this).data("order");
	    var status = $('.mls-tabs .link-status.active').attr('href');
	    var view = $('.mls-view .view-btn.active').data('view');
	    var element = "#mlsgrid";
	    var page = 1;
	    if ( $("#mls-pagination").length ){
	    	page = $('#mls-pagination a.active').attr('href').split('page=')[1];
	    }

	    var featured = 0;
		if ( $("#mls-featured").length ){
	    	featured = $("#mls-featured").val();
	    }

	    if (orderby == '') {
  			textButton = 'Default Order';
  		}else if (orderby == 'Price asc') {
  			textButton = 'Price - Low to High';
  		}else if (orderby == 'Price desc') {
  			textButton = 'Price - High to Low';
  		}else if (orderby == 'Date asc') {
  			textButton = 'Date - Old to New';
  		}else if (orderby == 'Date desc') {
  			textButton = 'Date - New to Old';
  		}

  		$('.mls-order a.dropdown-item').removeClass('active');
  		$(this).addClass('active');
  		$('.mls-order .filter-option-inner-inner').text(textButton);

	    GridPropertiesMLS(status, orderby, view, element, page, featured);
		$('.pagination-original').remove();
  	});


  	$(document).on('click', '.mls-view .view-btn', function (e) {
  		e.preventDefault();
	    var orderby = $('.mls-order a.dropdown-item.active').data('order');
	    var status = $('.mls-tabs .link-status.active').attr('href');
	    var view = $(this).data('view');
	    var element = "#mlsgrid";
	    var page = 1;
	    if ( $("#mls-pagination").length ){
	    	page = $('#mls-pagination a.active').attr('href').split('page=')[1];
	    }

	    var featured = 0;
		if ( $("#mls-featured").length ){
	    	featured = $("#mls-featured").val();
	    }

	    console.log('orderby: '+orderby+'status: '+status+'view: '+view);

	    $('.mls-view .view-btn').removeClass('active');
	    $(this).addClass('active');
	    
	    GridPropertiesMLS(status, orderby, view, element, page, featured);
		$('.pagination-original').remove();
  	});

  	$(document).on('click', '#mls-pagination a', function (e) {
  		e.preventDefault();
	    var orderby = $('.mls-order a.dropdown-item.active').data('order');
	    var status = $('.mls-tabs .link-status.active').attr('href');
	    var view = $('.mls-view .view-btn.active').data('view');
	    var element = "#mlsgrid";
	    var page = $(this).attr('href').split('page=')[1];

	    var featured = 0;
		if ( $("#mls-featured").length ){
	    	featured = $("#mls-featured").val();
	    }

	    console.log('orderby: '+orderby+' status: '+status+' view: '+view+' page: '+page);

	    $('#mls-pagination a').removeClass('active');
	    $(this).addClass('active');
	    
	    GridPropertiesMLS(status, orderby, view, element, page, featured);
		$('.pagination-original').remove();
  	});

	//   code for get data from condos grid
	  $(document).on('click', '#mls-pagination-condo a', function (e) {
		e.preventDefault();
	  var orderby = $('.condos-order a.dropdown-item.active').data('order');
	  var status = $('.condos-tabs .link-status.active').attr('href');
	  var view = $('.condos-view .view-btn.active').data('view');
	  var element = "#condogrid";
	  var page = $(this).attr('href').split('page=')[1];

	  var featured = 0;
	  if ( $("#mls-featured").length ){
		  featured = $("#mls-featured").val();
	  }

	  console.log('orderby: '+orderby+' status: '+status+' view: '+view+' page: '+page);

	  $('#mls-pagination-condo a').removeClass('active');
	  $(this).addClass('active');
	  
	  GridPropertiesMLS(status, orderby, view, element, page, featured);
	  $('.pagination-original').remove();
	});

  	/*--------------------------------------------------------------------------
     *  AutoComplete Search
    * -------------------------------------------------------------------------*/
    var blokhausreAutoComplete = function () {
        var ajaxCount = 0;
        var auto_complete_container = $('.auto-complete');
        var lastLenght = 0;

        $('input[name="keywordsearch"]').keyup(function(){

            var $this = $( this );
            var $form = $this.parents( 'form');
            var auto_complete_container = $form.find( '.auto-complete' );
            var keyword = $( this ).val();
            keyword = $.trim( keyword );
            var currentLenght = keyword.length;

            if ( currentLenght >= 2 && currentLenght != lastLenght ) {

                lastLenght = currentLenght;
                auto_complete_container.fadeIn();

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'blokhausre_get_auto_complete_search',
                        'key': keyword,
                        //'nonce' : HOUZEZ_ajaxcalls_vars.houzez_autoComplete_nonce
                    },
                    beforeSend: function( ) {
                        ajaxCount++;
                        if ( ajaxCount == 1 ) {
                            auto_complete_container.html('<div class="result"><p><i class="fa fa-spinner fa-spin fa-fw"></i> '+autosearch_text+ '</p></div>');
                        }
                    },
                    success: function(data) {
                        ajaxCount--;
                        if ( ajaxCount == 0 ) {
                            auto_complete_container.show();
                            if( data != '' ) {
                                auto_complete_container.empty().html(data).bind();
                            }
                        }
                    },
                    error: function(errorThrown) {
                        ajaxCount--;
                        if ( ajaxCount == 0 ) {
                            auto_complete_container.html('<div class="result"><p><i class="fa fa-spinner fa-spin fa-fw"></i> '+autosearch_text+ ' </p></div>');
                        }
                    }
                });

            } else {
                if ( currentLenght != lastLenght ) {
                    auto_complete_container.fadeOut();
                }
            }

        });
        auto_complete_container.on( 'click', 'li', function (){
            $('input[name="keywordsearch"]').val( $( this ).data( 'text' ) );
            auto_complete_container.fadeOut();
        }).bind();
    }
    blokhausreAutoComplete();

    $('#featured-carousel').each(function(){
        var faveOwl = $('#featured-carousel');

        faveOwl.owlCarousel({
            loop: true,
            autoplay: true,
            autoplaySpeed: 3000,
            dots: true,
            smartSpeed: 700,
            nav:true,
            navText : ['<i class="fa fa-angle-left" aria-hidden="true"></i>','<i class="fa fa-angle-right" aria-hidden="true"></i>'],
            items : 4,
            slidesToShow: 1,
            mouseDrag: true,
            responsive:{
                0: {
                    items: 1,
                    dots: false
                },
                320: {
                    items: 1,
                    dots: false
                },
                480: {
                    items: 1,
                    dots: false
                },
                768: {
                    items: 3
                },
                1000: {
                    items: 3
                }
                ,
                1920: {
                    items: 3
                }
            }
        });

        $('.btn-prev-A1Qhr').on('click',function(){
            faveOwl.trigger('prev.owl.carousel',[1000]);
        })
        $('.btn-next-A1Qhr').on('click',function(){
            faveOwl.trigger('next.owl.carousel');
        })

    });
});