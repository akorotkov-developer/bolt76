<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load('ui.bootstrap4');
?>

<link href="<?= $this->GetFolder(); ?>/suggestions.min.css" rel="stylesheet" />
<script src="<?= $this->GetFolder(); ?>/jquery.suggestions.min.js"></script>
<div class="bx_profile">
	<?
	ShowError($arResult["strProfileError"]);

	if ($arResult['DATA_SAVED'] == 'Y')
	{
		ShowNote(Loc::getMessage('PROFILE_DATA_SAVED'));
	}

	?>
	<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" role="form">
		<?=$arResult["BX_SESSION_CHECK"]?>
		<input type="hidden" name="lang" value="<?=LANG?>" />
		<input type="hidden" name="ID" value="<?=$arResult["ID"]?>" />
		<input type="hidden" name="LOGIN" value="<?=$arResult["arUser"]["LOGIN"]?>" />
		<div class="main-profile-block-shown" id="user_div_reg">
			<div class="main-profile-block-date-info">
				<?
				if($arResult["ID"]>0)
				{
					if ($arResult["arUser"]["TIMESTAMP_X"] <> '')
					{
						?>
						<div class="col-sm-9 col-md-offset-3 small">
							<strong><?=Loc::getMessage('LAST_UPDATE')?></strong>
							<strong><?=$arResult["arUser"]["TIMESTAMP_X"]?></strong>
						</div>
						<?
					}

					if ($arResult["arUser"]["LAST_LOGIN"] <> '')
					{
						?>
						<div class="col-sm-9 col-md-offset-3 small">
							<strong><?=Loc::getMessage('LAST_LOGIN')?></strong>
							<strong><?=$arResult["arUser"]["LAST_LOGIN"]?></strong>
						</div>
						<?
					}
				}
				?>
			</div>
			<?
			if (!in_array(LANGUAGE_ID,array('ru', 'ua')))
			{
				?>
				<div class="form-group">
					<label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-title"><?=Loc::getMessage('main_profile_title')?></label>
					<div class="col-sm-12">
						<input class="form-control" type="text" name="TITLE" maxlength="50" id="main-profile-title" value="<?=$arResult["arUser"]["TITLE"]?>" />
					</div>
				</div>
				<?
			}
			?>
			<div class="form-group">
				<label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-name"><?=Loc::getMessage('NAME')?></label>
				<div class="col-sm-12">
					<input class="form-control" type="text" name="NAME" maxlength="50" id="main-profile-name" value="<?=$arResult["arUser"]["NAME"]?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-last-name"><?=Loc::getMessage('LAST_NAME')?></label>
				<div class="col-sm-12">
					<input class="form-control" type="text" name="LAST_NAME" maxlength="50" id="main-profile-last-name" value="<?=$arResult["arUser"]["LAST_NAME"]?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-second-name"><?=Loc::getMessage('SECOND_NAME')?></label>
				<div class="col-sm-12">
					<input class="form-control" type="text" name="SECOND_NAME" maxlength="50" id="main-profile-second-name" value="<?=$arResult["arUser"]["SECOND_NAME"]?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-email"><?=Loc::getMessage('EMAIL')?></label>
				<div class="col-sm-12">
					<input class="form-control" type="text" name="EMAIL" maxlength="50" id="main-profile-email" value="<?=$arResult["arUser"]["EMAIL"]?>" />
				</div>
			</div>
            <div class="form-group">
                <label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-inn"><?=Loc::getMessage('UF_INN')?></label>
                <div class="col-sm-12">
                    <input class="form-control" type="text" name="UF_INN" maxlength="50" id="main-profile-inn" value="<?=$arResult["arUser"]["UF_INN"]?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-kpp"><?=Loc::getMessage('UF_KPP')?></label>
                <div class="col-sm-12">
                    <input class="form-control" type="text" name="UF_KPP" maxlength="50" id="main-profile-kpp" value="<?=$arResult["arUser"]["UF_KPP"]?>" />
                </div>
            </div>
			<div class="form-group">
				<label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-company"><?=Loc::getMessage('UF_COMPANY_NAME')?></label>
				<div class="col-sm-12">
					<input class="form-control" type="text" name="UF_COMPANY_NAME" maxlength="50" id="main-profile-company" value="<?=$arResult["arUser"]["UF_COMPANY_NAME"]?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-yuraddress"><?=Loc::getMessage('UF_YUR_ADDRESS')?></label>
				<div class="col-sm-12">
					<input class="form-control" type="text" name="UF_YUR_ADDRESS" maxlength="50" id="main-profile-yuraddress" value="<?=$arResult["arUser"]["UF_YUR_ADDRESS"]?>" />
				</div>
			</div>
			<?
			if ($arResult['CAN_EDIT_PASSWORD'])
			{
				?>
				<div class="form-group">
					<p class="main-profile-form-password-annotation col-sm-9 col-sm-offset-3 small">
						<?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?>
					</p>
				</div>
				<div class="form-group">
					<label class="main-profile-form-label col-sm-12 col-md-3 text-md-right" for="main-profile-password"><?=Loc::getMessage('NEW_PASSWORD_REQ')?></label>
					<div class="col-sm-12">
						<input class=" form-control bx-auth-input main-profile-password" type="password" name="NEW_PASSWORD" maxlength="50" id="main-profile-password" value="" autocomplete="off"/>
					</div>
				</div>
				<div class="form-group">
					<label class="main-profile-form-label main-profile-password col-sm-12 col-md-3 text-md-right" for="main-profile-password-confirm">
						<?=Loc::getMessage('NEW_PASSWORD_CONFIRM')?>
					</label>
					<div class="col-sm-12">
						<input class="form-control" type="password" name="NEW_PASSWORD_CONFIRM" maxlength="50" value="" id="main-profile-password-confirm" autocomplete="off" />
					</div>
				</div>
				<?
			}
			?>
		</div>
		<p class="main-profile-form-buttons-block col-sm-9 col-md-offset-3">
			<input type="submit" name="save" class="btn btn-themes btn-default btn-md main-profile-submit" value="<?=(($arResult["ID"]>0) ? Loc::getMessage("MAIN_SAVE") : Loc::getMessage("MAIN_ADD"))?>">
			&nbsp;
			<input type="submit" class="btn btn-themes btn-default btn-md"  name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
		</p>
	</form>
	<div class="col-sm-12 main-profile-social-block">
		<?
		if ($arResult["SOCSERV_ENABLED"])
		{
			$APPLICATION->IncludeComponent("bitrix:socserv.auth.split", ".default", array(
				"SHOW_PROFILES" => "Y",
				"ALLOW_DELETE" => "Y"
			),
				false
			);
		}
		?>
	</div>
	<div class="clearfix"></div>
	<script>
		BX.Sale.PrivateProfileComponent.init();
	</script>
</div>