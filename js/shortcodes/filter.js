(function($){
	$(document).ready( function() {
		FusionPageBuilderApp.mgs_forms_test_save_content = function(attributes){
			/*var $id = attributes.params.id,
				$class = attributes.params.class,
				$title = attributes.params.description,
				$href = attributes.params.full_image,
				$src = attributes.params.thumbnail_image,
				$alt = attributes.params.alt_text,
				$lightboxCode = '<a id="' + $id + '" class="' + $class + '" title="' + $title + '" href="' + $href + '" data-rel="prettyPhoto"><img src="' + $src + '" alt="' + $alt + '" /></a>';

			attributes.params.element_content = $lightboxCode;
			*/
			if( attributes.params.tipo==='code' ){
				attributes.params.code = FusionPageBuilderApp.base64Encode(attributes.params.code);
			}
			console.log('fitros', attributes);
			console.log('tipo', attributes.params.code);
			return attributes;
		}
	});
}(jQuery));
