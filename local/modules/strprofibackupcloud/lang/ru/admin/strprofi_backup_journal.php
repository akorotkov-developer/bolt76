<?php

$MESS['DUMP_MAIN_MAKE_ARC'] = 'Резервное копирование';
$MESS['MAKE_DUMP_FULL'] = 'Настройки автоматического резервного копирования';
$MESS['DUMP_MAIN_PARAMETERS'] = 'Параметры';
$MESS['DUMP_MAIN_EXPERT_SETTINGS'] = 'Настройки резервного копирование';
$MESS['DUMP_MAIN_JOURNAL'] = 'Журнал';
$MESS['DUMP_MAIN_JOURNAL_TITLE'] = 'Журнал резервных копий';

$MESS['DUMP_MAIN_ARC_CONTENTS'] = 'Содержимое резервной копии';
$MESS['DUMP_MAIN_ARC_DATABASE'] = 'Архивировать базу данных';
$MESS['MAIN_DUMP_BASE_SIZE'] = 'МБ';
$MESS['MAIN_DUMP_BASE_STAT'] = 'статистику';
$MESS['MAIN_DUMP_BASE_SINDEX'] = 'поисковый индекс';
$MESS['MAIN_DUMP_EVENT_LOG'] = 'журнал событий';
$MESS['MAIN_DUMP_FILE_KERNEL'] = 'Архивировать ядро:';
$MESS['MAIN_DUMP_FILE_PUBLIC'] = 'Архивировать публичную часть:';
$MESS['MAIN_DUMP_MASK'] = 'Исключить из архива файлы и директории по маске:';
$MESS['MAIN_DUMP_MORE'] = 'Ещё...';
$MESS['MAIN_DUMP_FILE_MAX_SIZE'] = 'Исключить из архива файлы размером более (0 - без ограничения):';
$MESS['MAIN_DUMP_FILE_STEP_sec'] = 'сек.';
$MESS['MAIN_DUMP_FILE_MAX_SIZE_b'] = 'б ';
$MESS['MAIN_DUMP_FILE_MAX_SIZE_kb'] = 'кб ';
$MESS['MAIN_DUMP_FILE_MAX_SIZE_mb'] = 'Мб ';
$MESS['MAIN_DUMP_FILE_MAX_SIZE_gb'] = 'Гб ';
$MESS['DUMP_MAIN_ARC_MODE'] = 'Режим архивации';
$MESS['MAIN_DUMP_ENABLE_ENC'] = 'Шифровать данные резервной копии:';
$MESS['INTEGRITY_CHECK_OPTION'] = 'Проверить целостность архива после завершения:';
$MESS['DISABLE_GZIP'] = 'Отключить компрессию архива (снижение нагрузки на процессор):';
$MESS['STEP_LIMIT'] = 'Длительность шага:';
$MESS['MAIN_DUMP_FILE_STEP_SLEEP'] = 'интервал:';
$MESS['MAIN_DUMP_MAX_ARCHIVE_SIZE'] = 'Максимальный размер несжатых данных в одной части архива (МБ):';
$MESS['MAIN_DUMP_MAX_ARCHIVE_SIZE_VALUES'] = 'допустимые значения: 11 - 2047';
$MESS['DUMP_MAIN_MULTISITE_INFO'] = 'Если выбрано несколько сайтов для помещения в архив, в корне архива будет лежать первый по списку сайт, а публичные части остальных сайтов будут помещены в папку <b>/bitrix/backup/sites</b>. При восстановлении нужно будет вручную скопировать их в нужные папки и создать символьные ссылки.';
$MESS['MAIN_DUMP_FOOTER_MASK'] = 'Для маски исключения действуют следующие правила:
	<p>
	<li>шаблон маски может содержать символы &quot;*&quot;, которые соответствуют любому количеству любых символов в имени файла или папки;</li>
	<li>если в начале стоит косая черта (&quot;/&quot; или &quot;\\&quot;), путь считается от корня сайта;</li>
	<li>в противном случае шаблон применяется к каждому файлу или папке;</li>
	<p>Примеры шаблонов:</p>
	<li>/content/photo - исключить целиком папку /content/photo;</li>
	<li>*.zip - исключить файлы с расширением &quot;zip&quot;;</li>
	<li>.access.php - исключить все файлы &quot;.access.php&quot;;</li>
	<li>/files/download/*.zip - исключить файлы с расширением &quot;zip&quot; в директории /files/download;</li>
	<li>/files/d*/*.ht* - исключить файлы из директорий, начинающихся на &quot;/files/d&quot;  с расширениями, начинающимися на &quot;ht&quot;.</li>
	';
$MESS['MAIN_DUMP_MAX_ARCHIVE_SIZE_INFO'] = 'Системные ограничения php не позволяют делать размер одной части архива более 2 Гб. Не устанавливайте это значение больше 200 Мб т.к. это существенно увеличивает время архивации и распаковки, оптимальное значение: 100 Мб.';
$MESS['MAIN_DUMP_TIME_CREATE_BACKUP'] = 'Время создания резервной копии';
$MESS['MAIN_DUMP_PERIOD'] = 'Периодичность';
$MESS['MAIN_DUMP_SCHEDULE'] = 'Расписание';
$MESS['MAIN_DUMP_DISK'] = 'Место загрузки';
$MESS['MAIN_DUMP_YANDEX_DISK'] = 'Яндекс.Диск';
$MESS['MAIN_DUMP_DELETE_OLD'] = 'Удаление старых копий';
$MESS['MAIN_DUMP_EVERY_DAY'] = 'каждый день';
$MESS['MAIN_DUMP_AFTER_DAY'] = 'через день';
$MESS['MAIN_DUMP_EVERY_3_DAY'] = 'каждые 3 дня';
$MESS['MAIN_DUMP_EVERY_WEEK'] = 'еженедельно';
$MESS['MAIN_DUMP_IS_DELETE_OLD_COPY'] = 'Удалять старые копии';
$MESS['MAIN_DUMP_SHED_TIME_SET'] = 'Настройка времени создания резервной копии доступна в случае если системные агенты выполняются на cron (неважно, только непериодические или все). Иначе для автоматического создания резервных копий необходимо настроить на определенное время выполнение php скрипта <b>/bitrix/modules/main/tools/backup.php</b> через панель хостинга.';

