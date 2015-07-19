# DLE-EditRating
Модуль редактирования рейтинга для DLE

![version](https://img.shields.io/badge/version-1.0.0-red.svg?style=flat-square "Version")
![DLE](https://img.shields.io/badge/DLE-9.6+-green.svg?style=flat-square "DLE Version")
[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://github.com/pafnuty/DLE-EditRating/blob/master/LICENSE)

## Назнчение
Модуль предназначен для редактирования или очистки рейтинга у новостей (комментарии в планах) на сайте под управлением CMS DataLife Engine.

## Установка
- Распаковать содержимое папки **upload** в корень сайта.
- В **main.tpl**, в нужном месте прописать стили и скрипты модуля:
```smarty
[group=1,2]
<link href="{THEME}/editrating/css/editrating.css" rel="stylesheet" />
<script type="text/javascript" src="/engine/classes/min/index.php?charset=utf-8&amp;f={THEME}/editrating/js/jquery.magnificpopup.min.js,{THEME}/editrating/js/jquery.ladda.min.js,{THEME}/editrating/js/jquery.form.min.js,{THEME}/editrating/js/editrating.js&amp;01"></script>
[/group]
```
- В шаблоне полной и/или краткой новости, в нужно месте прописать: `[group=1,2]<span data-er-edit="{news-id}" class="er-btn">Редактировать рейтинг</span>[/group]`.
- Если необходимо — настроить доступ групп в файле `/engine/data/editrating_config.php`.
