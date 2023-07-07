<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

ShowMessage($arParams["~AUTH_RESULT"]);

?>
<form name="bform" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
<?
if (strlen($arResult["BACKURL"]) > 0)
{
?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?
}
?>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="SEND_PWD">

	<p><?echo GetMessage("sys_forgot_pass_label")?></p>

	<div style="margin-top: 16px">
		<div></div>
		<div>
            <div class="form-group">
                <label for="usstring"><b><?=GetMessage("sys_forgot_pass_login1")?></b></label>
                <input type="text" class="form-control" id="usstring" name="USER_LOGIN" value="<?=$arResult["USER_LOGIN"]?>" />
                <input type="hidden" name="USER_EMAIL" />
            </div>
		</div>
		<div><?echo GetMessage("sys_forgot_pass_note_email")?></div>
	</div>

<?if($arResult["PHONE_REGISTRATION"]):?>
	<div style="margin-top: 16px">
        <div class="form-group">
            <label for="usphone"><b><?=GetMessage("sys_forgot_pass_phone")?></b></label>
            <input class="form-control" id="usphone" type="text" name="USER_PHONE_NUMBER" value="<?=$arResult["USER_PHONE_NUMBER"]?>" />
        </div>
		<div><?echo GetMessage("sys_forgot_pass_note_phone")?></div>
	</div>
<?endif;?>

<?if($arResult["USE_CAPTCHA"]):?>
	<div style="margin-top: 16px">
		<div>
			<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
		</div>
		<div><?echo GetMessage("system_auth_captcha")?></div>
		<div><input type="text" name="captcha_word" maxlength="50" value="" /></div>
	</div>
<?endif?>
	<div style="margin-top: 20px">
		<input type="submit" class="btn btn-primary" name="send_account_info" value="<?=GetMessage("AUTH_SEND")?>" />
	</div>
</form>

<div style="margin-top: 16px">
	<p><a href="/account/auth/"><b><?=GetMessage("AUTH_AUTH")?></b></a></p>
</div>

<script type="text/javascript">
document.bform.onsubmit = function(){document.bform.USER_EMAIL.value = document.bform.USER_LOGIN.value;};
document.bform.USER_LOGIN.focus();
</script>
