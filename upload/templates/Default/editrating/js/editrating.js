/*!
=============================================================================
DLE EditRating — редактирование рейтинга для DLE
=============================================================================
Автор:   ПафНутиЙ
URL:     http://pafnuty.name/
twitter: https://twitter.com/pafnuty_name
google+: http://gplus.to/pafnuty
email:   pafnuty10@gmail.com
=============================================================================
*/

// Экономим памть браузера.
var doc = $(document);

doc
	// ajax-отправка формы + эффекты
	.on('submit', '[data-er-form]', function () {
		var $this = $(this),
			laddaLoad,
			options = {
				beforeSubmit: erStart,
				success: erDone,
			};

		$this.ajaxSubmit(options);

		return false;
	})
	// Открытие ajax-окна с формой
	.on('click', '[data-er-edit]', function (e) {
		var $this = $(this),
			id = $this.data('erEdit');

		$.magnificPopup.open({
			items: {
				src: '/engine/ajax/editrating.php',
			},
			focus: '.er-input-first',
			type: 'ajax',
			ajax: {
				settings: {
					data: {
						id: id
					}
				}
			}
		});
		return false;
	});


// Функция, выполняемая перед отправкой формы
function erStart(formData, jqForm) {
	laddaLoad = jqForm.find('.ladda-button').ladda();
	laddaLoad.ladda('start');

	return true;
}

// Функция, выполняемая после удачной отправки формы
function erDone(responseText, statusText, xhr, $form) {

	var $responseText = $(responseText),
		responseResult = ($responseText.is('form')) ? $responseText.html() : responseText;

	if (statusText == 'success') {
		laddaLoad.ladda('stop');
		$form.html(responseResult);
	}
}
