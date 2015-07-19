<form action="/engine/ajax/editrating.php" method="post" data-er-form>
	<input type="hidden" name="done" value="yes">	
	<input type="hidden" name="id" value="{id}">	

	<div class="er-wrapper">
		<span class="mfp-close">&times;</span>
		<div class="er-header">
			[success]
				Готово!
			[/success]
			[error]
				Ошибка!
			[/error]
			[form]
				<small>Редактирование рейтинга новости:</small><br> {title}
			[/form]
		</div>
		
		<div class="er-content">
			[success]
				<div class="er-alert er-alert-success">
					Рейтинг успешно отредактирован!
				</div>
				<h3>Новый рейтинг</h3>
				[rating-type-1]
					<div class="er-field">
						<div class="er-label">Кол-во голосов:</div> 
						<div class="er-field-input">{vote_num}</div>
					</div>
				[/rating-type-1]
				<div class="er-field">
					<div class="er-label">[rating-type-1]Значение рейтинга:[/rating-type-1][not-rating-type-1]Кол-во лайков[/not-rating-type-1]</div> 
					<div class="er-field-input">{rating}</div>
				</div>
			[/success]
			[error]
				<div class="er-alert er-alert-error">
					{error_text}
				</div>
			[/error]
			[form]
				<h3 class="mt0">Текущий рейтинг</h3>
				[rating-type-1]
					<div class="er-field">
						<div class="er-label">Кол-во голосов:</div> 
						<div class="er-field-input">{vote_num}</div>
					</div>
				[/rating-type-1]
				<div class="er-field">
					<div class="er-label">[rating-type-1]Значение рейтинга:[/rating-type-1][not-rating-type-1]Кол-во лайков[/not-rating-type-1]</div> 
					<div class="er-field-input">{rating}</div>
				</div>

				<hr>
				<h3>Установить новый рейтинг</h3>
				
				[rating-type-1]
					<div class="er-field">
						<div class="er-label">Кол-во голосов:</div> 
						<div class="er-field-input">
							<input class="er-input er-input-first" type="number" name="vote_num" min="0" value="{vote_num}">
						</div>
					</div>

					<div class="er-field">
						<div class="er-label">Значение рейтинга:</div> 
						<div class="er-field-input">
							<input class="er-input" type="number" name="rating" max="5" min="1" value="{rating}">
						</div>
					</div>
				[/rating-type-1]
				[not-rating-type-1]
					<input type="hidden" name="rating" value="1">
					<div class="er-field">
						<div class="er-label">Кол-во лайков</div> 
						<div class="er-field-input">
							<input class="er-input er-input-first" type="number" name="vote_num" [rating-type-2]min="0"[/rating-type-2] value="{vote_num}">
						</div>
					</div>
				[/not-rating-type-1]

				<div class="er-field">
					<div class="er-label">&nbsp;</div> 
					<div class="er-field-input">
						<p>
							<input type="checkbox" name="clear_rating" value="yes" id="clear_rating"> <label for="clear_rating">Очистить рейтинг</label>
						</p>
						<button class="er-btn ladda-button" type="submit" data-style="zoom-out"><span class="ladda-label">Сохранить</span></button>
					</div>
				</div>
			[/form]

		</div>		
	</div>
</form>