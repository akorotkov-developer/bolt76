<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Ошибка 404");
?> К сожалению, запрашиваемая Вами страница не найдена.
<div>
  <br />
</div>

<div>Попробуйте найти интересующий Вас раздел в меню или начать с <a href="/" >главной страницы</a>.</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>