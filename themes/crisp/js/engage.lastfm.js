(function($){
	$.fn.lastFM = function(options) {
		
		var defaults = {
			number: 5,
			username: 'rbnvrw',
			apikey: 'ef17324ac969c161d1e5be4f3b35f6a9',
			artSize: 'medium',
			onComplete: function(container, wrapper){ 
				container.append(wrapper);
			}
		},
		settings = $.extend({}, defaults, options);

		var lastUrl = 'http://ws.audioscrobbler.com/2.0/?method=user.gettopartists&user='+settings.username+'&api_key='+settings.apikey+'&limit='+settings.number+'&format=json&callback=?';
		var current = $(this);
		
		var wrapper = $('<ul class="list-group"></ul>');
		var container = $('<li class="list-group-item"><a href="#" class="lastfm__link" target="_blank" rel="external"></a></li>');
		
		if(settings.artSize == 'small'){imgSize = 0}
		if(settings.artSize == 'medium'){imgSize = 1}
		if(settings.artSize == 'large'){imgSize = 2}

		this.each(function() {
			
			$.getJSON(lastUrl, function(data){ 
				
				$.each(data.topartists.artist, function(i, item){

					url = stripslashes(item.url);
					name = item.name;
					playcount = item.playcount;
					
					wrapper.append(container.clone());
					
					var currentBlock = wrapper.children(':eq('+i+')');
					var currentLink = currentBlock.find('.lastfm__link');
					
					if(item.image[1]['#text'] == ''){
						currentLink.addClass('lastfm__link--noimg');
						art = '';
					}else{
						artsrc = stripslashes(item.image[imgSize]['#text']);
						art = "<img src='"+artsrc+"' alt='"+name+"' class='lastfm__img' />";
					}

					currentLink.attr('href', url);
										
					currentLink.append(art);
					currentLink.append(name);
					currentBlock.append('<span class="badge">'+playcount+'</span>');
					
					//callback
					if(i==(settings.number-1)){
						settings.onComplete(current, wrapper);
					}
					
				});
			});
		});
	};
	
	//Clean up the URL's
	function stripslashes( str ) {	 
		return (str+'').replace(/\0/g, '0').replace(/\\([\\'"])/g, '$1');
	}
})(jQuery);

$(document).ready(function(){
    $('#lastfm').lastFM();
});
