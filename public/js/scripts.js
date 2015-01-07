
jQuery(document).ready(function() {

	jQuery('.fancybox').each(function() {
	
		var width = jQuery(this).attr("width");
		if (!width) width = 700;
	
		jQuery(this).fancybox({
		    'width': width,
		    'autoDimensions': false,
		    'type': 'iframe'
		});
		
	});

    jQuery('.confirmation-modal').each(function() {

        jQuery(this).fancybox({
            'width': '500',
            'autoDimensions': false,
            'hideOnContentClick': false,
            'closeBtn': false,
            'helpers' : {
                'overlay' : {
                    'closeClick': false
                }
            },
            'keys' : {
                close  : null
            },
            'beforeShow': function () {
                jQuery.fancybox.wrap.bind("contextmenu", function (e) {
                    return false;
                });
            }
        });

    });

	$('ul#tabs-nav').each(function() {
		var $active, $content, $links = $(this).find('a');
		
		$active = $($links.filter('[href="'+location.hash+'"]')[0] || $links[0]);
		$active.addClass('active');
		
		$content = $($active[0].hash);
		
		$links.not($active).each(function () {
			$(this.hash).hide();
		});
		
		$(this).on('click', 'a', function(e){
			$active.removeClass('active');
			$content.hide();
			
			$active = $(this);
			$content = $(this.hash);
			
			$active.addClass('active');
			$content.show();
			
			e.preventDefault();
		});
	});

	/*jQuery('#click5-newsletter').validate({ 
		errorPlacement: function(error, element) {} 
	});*/

});
