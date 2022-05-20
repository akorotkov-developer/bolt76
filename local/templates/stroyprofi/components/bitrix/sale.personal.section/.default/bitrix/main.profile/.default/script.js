BX.namespace('BX.Sale.PersonalProfileComponent');

(function() {
	BX.Sale.PrivateProfileComponent = {
		init: function ()
		{
			var passwordNode = BX('main-profile-password');
			var confirmNode = BX('main-profile-password-confirm');
			BX.ready(function(){
				BX.bind(confirmNode, 'input', function(){
					if (!BX.type.isNotEmptyString(confirmNode.value))
					{
						BX.removeClass(passwordNode.parentNode, 'has-error');
					}
					else if (!BX.type.isNotEmptyString(passwordNode.value))
					{
						BX.addClass(passwordNode.parentNode, 'has-error');
					}
				});
				BX.bind(passwordNode, 'input', function(){
					if (BX.type.isNotEmptyString(passwordNode.value))
					{
						BX.removeClass(passwordNode.parentNode, 'has-error');
					}
					else if (BX.type.isNotEmptyString(confirmNode.value))
					{
						BX.addClass(passwordNode.parentNode, 'has-error');
					}
				})
			});
		},
	}
})();

$( document ).ready(function() {
	var sToken = "ed7f02d17e73afff8b0621a1b1b5a5a100d06672";
	$("input[name='UF_INN']").suggestions({
		token: sToken,
		type: "party",
		onSelect: function(suggestion) {
			$("input[name='UF_INN']").val(suggestion.data.inn);
			$("input[name='UF_KPP']").val(suggestion.data.kpp);
			$("input[name='UF_COMPANY_NAME']").val(suggestion.data.name.short_with_opf);
			$("input[name='UF_YUR_ADDRESS']").val(suggestion.data.address.value);
		}
	});
});