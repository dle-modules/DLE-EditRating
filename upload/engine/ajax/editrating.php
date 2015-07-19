<?php
/*
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

@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -12));
define('ENGINE_DIR', ROOT_DIR . '/engine');

/**
 * @var array $config
 */
include ENGINE_DIR . '/data/config.php';

/**
 * @var array $editRatingConfig
 */
include ENGINE_DIR . '/data/editrating_config.php';

if ($config['version_id'] > 10.2) {
	date_default_timezone_set($config['date_adjust']);
	$_TIME = time();
} else {
	$_TIME = time() + ($config['date_adjust'] * 60);
}

if ($config['http_home_url'] == '') {
	$protocol                = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
	$config['http_home_url'] = explode('engine/ajax/' . basename(__FILE__), $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = $protocol . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

if ($config['version_id'] > 9.6) {
	dle_session();
} else {
	@session_start();
}

$_TIME = time();

require_once ENGINE_DIR . '/modules/sitelogin.php';

if (!$is_logged) {
	die('you not logged');
}

$id = (int) $_REQUEST['id'];

if (!$id) {
	die('error');
}

//################# Определение групп пользователей
$user_group = get_vars('usergroup');

if (!$user_group) {
	$user_group = array();

	$db->query("SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC");

	while ($row = $db->get_row()) {

		$user_group[$row['id']] = array();

		foreach ($row as $key => $value) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}

	}
	set_vars('usergroup', $user_group);
	$db->free();
}

// Проверяем группу пользователей
$allowedGroups = explode(',', $editRatingConfig['allowedGroups']);

if (!in_array($user_group[$member_id['user_group']]['id'], $allowedGroups)) {
	die('Hacking attempt!');
}

header('Content-type: text/html; charset=' . $config['charset']);

$template_dir = ROOT_DIR . '/templates/' . $config['skin'] . '/editrating';

if (file_exists($template_dir . '/edit.tpl')) {

	$tpl->result['editRating'] = '';

	require_once ENGINE_DIR . '/classes/templates.class.php';
	$tpl      = new dle_template();
	$tpl->dir = $template_dir;
	define('TEMPLATE_DIR', $tpl->dir);

	$tpl->load_template('edit.tpl');

	// Обрабатываем комментарии в шаблоне, которые не должны попасть в вывод
	$tpl->copy_template = preg_replace("'\\{\\*(.*?)\\*\\}'si", '', $tpl->copy_template);

	// Выводим данные из реквеста
	$tpl->set('{request}', print_r($_REQUEST, true));

	// Данные об ошибке.
	$error     = true;
	$errorText = array();

	$ratingInfo = $db->super_query('SELECT p.id, p.title, e.rating, e.vote_num FROM ' . PREFIX . '_post p LEFT JOIN ' . PREFIX . '_post_extras e ON (p.id=e.news_id) WHERE id = ' . $id);

	if ($ratingInfo['id'] > 0) {
		$error = false;
	} else {
		$errorText[] = 'ID новости не найден';
	}

	// Если ошибок нет
	if (!$error) {

		if (isset($_POST['done'])) {
			// Если даные отправлены через post и есть нужное нам поле done - изменяем рейтинг новости
			if (isset($_POST['clear_rating'])) {
				// Если нужно - очищаем рейтинг
				$db->query("UPDATE " . PREFIX . "_post_extras SET rating='', vote_num=0 WHERE news_id ='{$ratingInfo['id']}'");

				$db->query("DELETE FROM " . PREFIX . "_logs WHERE news_id ='{$ratingInfo['id']}'");

				// Получаем новые данные
				$ratingInfo = $db->super_query('SELECT p.id, p.title, e.rating, e.vote_num FROM ' . PREFIX . '_post p LEFT JOIN ' . PREFIX . '_post_extras e ON (p.id=e.news_id) WHERE id = ' . $id);

				$tpl->set_block("'\\[error\\](.*?)\\[/error\\]'si", '');
				$tpl->set_block("'\\[form\\](.*?)\\[/form\\]'si", '');
				$tpl->set('[success]', '');
				$tpl->set('[/success]', '');
				$tpl->set('{error_text}', '');

				if ($config['allow_alt_url'] AND !$config['seo_type']) {
		            $cprefix = 'full_';
		        } else {
		            $cprefix = 'full_' . $news_id;
		        }

		        clear_cache(array('news_', 'rss', $cprefix));

			} else {
				// Проверяем значение рейтинга
				if (isset($_POST['rating'])) {
					$postRating = (int) $_POST['rating'];
					if (!$config['rating_type']) {
						if ($postRating > 5 or $postRating < 1) {
							$error       = true;
							$errorText[] = 'Значение рейтинга можно указывать в диапазоне от 1 до 5';
						}
					}

					if ($config['rating_type'] == '1') {
						$postRating = 1;
					}

					// if ($config['rating_type'] == '2') {
					// 	if ($postRating != 1 AND $postRating != -1) {
					// 		$error       = true;
					// 		$errorText[] = 'Для значения рейтинга можно указывать либо "1", либо "-1"';
					// 	}
					// }

					// if ($postRating == 0) {
					// 	$error       = true;
					// 	$errorText[] = 'Значение рейтинга не может быть нулевым';
					// }

				} else {
					$error       = true;
					$errorText[] = 'Значение рейтинга не указано';
				}

				// Проверяем голоса

				if (isset($_POST['vote_num'])) {
					$postVotes = (int) $_POST['vote_num'];

					if ($postVotes < 0 && $config['rating_type'] != '2') {
						$error       = true;
						$errorText[] = 'Количество голосов не может быть отрицательным';
					}
				}

				if ($error) {
					$tpl->set_block("'\\[form\\](.*?)\\[/form\\]'si", '');
					$tpl->set_block("'\\[success\\](.*?)\\[/success\\]'si", '');
					$tpl->set('[error]', '');
					$tpl->set('[/error]', '');
					$tpl->set('{error_text}', '<ul><li>' . implode('</li><li>', $errorText) . '</li></ul>');
				} else {
					if ($config['rating_type'] == '1' AND $ratingInfo['rating'] < 0) {
						$db->query("UPDATE " . PREFIX . "_post_extras SET rating='{$postRating}', vote_num='1' WHERE news_id ='{$ratingInfo['id']}'");
					} else {
						$postRating = $postRating * $postVotes;

						$db->query("UPDATE " . PREFIX . "_post_extras SET rating='{$postRating}', vote_num='{$postVotes}' WHERE news_id ='{$ratingInfo['id']}'");
					}

					$ratingInfo = $db->super_query('SELECT p.id, p.title, e.rating, e.vote_num FROM ' . PREFIX . '_post p LEFT JOIN ' . PREFIX . '_post_extras e ON (p.id=e.news_id) WHERE id = ' . $id);

					$tpl->set_block("'\\[form\\](.*?)\\[/form\\]'si", '');
					$tpl->set_block("'\\[error\\](.*?)\\[/error\\]'si", '');
					$tpl->set('[success]', '');
					$tpl->set('[/success]', '');
					$tpl->set('{error_text}', '');

					if ($config['allow_alt_url'] AND !$config['seo_type']) {
			            $cprefix = 'full_';
			        } else {
			            $cprefix = 'full_' . $news_id;
			        }

			        clear_cache(array('news_', 'rss', $cprefix));

				}

			}
		} else {

			$tpl->set_block("'\\[error\\](.*?)\\[/error\\]'si", '');
			$tpl->set_block("'\\[success\\](.*?)\\[/success\\]'si", '');
			$tpl->set('[form]', '');
			$tpl->set('[/form]', '');
		}



		if (!$config['rating_type']) {
			$ratingInfo['rating'] = round($ratingInfo['rating'] / $ratingInfo['vote_num'], 0);
		}

		$tpl->set('{id}', $ratingInfo['id']);
		$tpl->set('{rating}', $ratingInfo['rating']);
		$tpl->set('{vote_num}', $ratingInfo['vote_num']);
		$tpl->set('{title}', stripslashes($ratingInfo['title']));

	} else {
		// Когда есть ошибки
		$tpl->set_block("'\\[form\\](.*?)\\[/form\\]'si", '');
		$tpl->set_block("'\\[success\\](.*?)\\[/success\\]'si", '');
		$tpl->set('[error]', '');
		$tpl->set('[/error]', '');

		$tpl->set('{error_text}', '<ul><li>' . implode('</li><li>', $errorText) . '</li></ul>');
	}

	if(!$config['rating_type']) {
		$tpl->set_block("'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", '');
		$tpl->set_block("'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", '');
		$tpl->set('[rating-type-1]', '');
		$tpl->set('[/rating-type-1]', '');

		$tpl->set_block("'\\[not-rating-type-1\\](.*?)\\[/not-rating-type-1\\]'si", '');
		$tpl->set('[not-rating-type-2]', '');
		$tpl->set('[/not-rating-type-2]', '');
		$tpl->set('[not-rating-type-3]', '');
		$tpl->set('[/not-rating-type-3]', '');

	} elseif ($config['rating_type'] == '1') {
		$tpl->set_block("'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", '');
		$tpl->set_block("'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", '');
		$tpl->set('[rating-type-2]', '');
		$tpl->set('[/rating-type-2]', '');

		$tpl->set_block("'\\[not-rating-type-2\\](.*?)\\[/not-rating-type-2\\]'si", '');
		$tpl->set('[not-rating-type-1]', '');
		$tpl->set('[/not-rating-type-1]', '');
		$tpl->set('[not-rating-type-3]', '');
		$tpl->set('[/not-rating-type-3]', '');

	} else {
		$tpl->set_block("'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", '');
		$tpl->set_block("'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", '');
		$tpl->set('[rating-type-3]', '');
		$tpl->set('[/rating-type-3]', '');

		$tpl->set_block("'\\[not-rating-type-3\\](.*?)\\[/not-rating-type-3\\]'si", '');
		$tpl->set('[not-rating-type-2]', '');
		$tpl->set('[/not-rating-type-2]', '');
		$tpl->set('[not-rating-type-1]', '');
		$tpl->set('[/not-rating-type-1]', '');

	}

	$tpl->compile('editRating');
	$editRating = $tpl->result['editRating'];

	$tpl->clear();
	$db->close();
	echo $editRating;

} else {
	die('edit.tpl not found');
}
