(function ($){
	$.fn.sidrahAutoComplete = function(options, callback){

		var settings = $.extend({
			'type': 'mother',
			'suggested': [],
			'class_to_walk': 'dummy',
			'input_class': 'large-12 columns',
		}, options);

		return this.each(function(){

			var uniqueId = Math.floor(Math.random()*99999);
			var object = $(this);
			var objVal = object.val();
			var inputName = object.attr('name');
			var mouseInDiv = false;
			
			if (inputName == '')
			{
				return;
			}
			
			object.addClass(settings['input_class']);
			
			object.attr('autocomplete', "off");
			object.attr('name', '');
			object.attr('data-id', '');

			object.before('<a name="autocomplete-names-' + uniqueId + '"></a>'); 
			object.after('<div class="autocomplete ' + settings['input_class'] + '" id="idnames-' + uniqueId + '"></div>');
			object.after('<input type="hidden" name="' + inputName + '" class="name_value ' + settings['class_to_walk'] + '" id="valuename-' + uniqueId + '" value="' + objVal + '"/>');

			$('#idnames-' + uniqueId).hide();

			object.bind("keyup", function(event){

				objVal = object.val();
				
				$('#valuename-' + uniqueId).val('');

				object.attr('data-id', '');
				
				if (callback != undefined){
					callback.call(object);
				}

				$.ajax({
					url: 'sidrah_autocomplete.php?name=' + encodeURIComponent(objVal) + '&type=' + settings['type'] + '&suggested=' + JSON.stringify(settings['suggested']) + '&unique_id=' + uniqueId,
					success: function(data){

						$('#idnames-' + uniqueId).show();
						$('#idnames-' + uniqueId).html(data);

						$('#idnames-' + uniqueId + ' .result').click(function(){
						
							objVal = $(this).attr('title');
							objId = $(this).attr('data-id');
	
							$('#valuename-' + uniqueId).val(objVal);
							$('#idnames-' + uniqueId).hide();

							object.val(objVal);
							object.attr('data-id', objId);

							if (callback != undefined){
								callback.call(object);
							}

							return false;
						});
					}
				});
			});
			
			object.focusout(function(){
				if (mouseInDiv == false)
				{
					$('#idnames-' + uniqueId).hide();
					
					if ($('#valuename-' + uniqueId).val() == "")
					{
						object.val("");
					}
				}
			});
			
			$('#idnames-' + uniqueId).mouseover(function(){
				mouseInDiv = true;
			});
			
			$('#idnames-' + uniqueId).mouseout(function(){
				mouseInDiv = false;
			});
		});
	}
})(jQuery);
