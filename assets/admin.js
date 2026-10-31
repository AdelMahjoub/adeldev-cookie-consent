/**
 * Adeldev Cookie Consent — settings page: color pickers and bubble image.
 */
jQuery(function ($) {
	$('.adeldev-cc-color').wpColorPicker();

	$('.adeldev-cc-image').each(function () {
		var box = $(this);
		var input = box.find('input[type=hidden]');
		var img = box.find('img');
		var remove = box.find('.adeldev-cc-image-remove');
		var frame;

		box.find('.adeldev-cc-image-choose').on('click', function () {
			if (!frame) {
				frame = wp.media({
					title: 'Image de la bulle',
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function () {
					var file = frame.state().get('selection').first().toJSON();
					var size = file.sizes && file.sizes.thumbnail ? file.sizes.thumbnail : file;
					input.val(file.id);
					img.attr('src', size.url).css({ display: '', verticalAlign: 'middle', borderRadius: '50%', objectFit: 'cover' });
					remove.show();
				});
			}
			frame.open();
		});

		remove.on('click', function () {
			input.val('');
			img.attr('src', '').hide();
			remove.hide();
		});
	});
});
