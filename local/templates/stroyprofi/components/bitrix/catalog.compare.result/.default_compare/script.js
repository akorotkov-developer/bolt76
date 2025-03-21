BX.namespace("BX.Iblock.Catalog");

BX.Iblock.Catalog.CompareClass = (function()
{
	var CompareClass = function(wrapObjId, reloadUrl, windowObj)
	{
		this.wrapObjId = wrapObjId;
		this.reloadUrl = reloadUrl;
		this.windowObj = windowObj;
		BX.addCustomEvent(window, 'onCatalogDeleteCompare', BX.proxy(this.reload, this))

		this.refreshTabs();
	};

	CompareClass.prototype.MakeAjaxAction = function(url)
	{
		BX.showWait(BX(this.wrapObjId));

		this.activeTabId =  document.querySelector('.tabs-nav .active').getAttribute('id');

		BX.ajax.post(
			url,
			{
				ajax_action: 'Y'
			},
			BX.proxy(this.reloadResult, this)
		);
	};

	CompareClass.prototype.reload = function()
	{
		BX.showWait(BX(this.wrapObjId));

		this.activeTabId =  document.querySelector('.tabs-nav .active').getAttribute('id');

		BX.ajax.post(
			this.reloadUrl,
			{
				compare_result_reload: 'Y'
			},
			BX.proxy(this.reloadResult, this)
		);
	};

	CompareClass.prototype.reloadResult = function(result)
	{
		BX.closeWait();
		BX(this.wrapObjId).innerHTML = result;

		this.refreshTabs();
		this.clickActiveTab();
	};

	CompareClass.prototype.clickActiveTab = function()
	{
		if (this.activeTabId) {
			document.querySelector('[id="' + this.activeTabId + '"]').click();
		}
	}

	CompareClass.prototype.delete = function(url)
	{
		BX.showWait(BX(this.wrapObjId));

		this.activeTabId =  document.querySelector('.tabs-nav .active').getAttribute('id');

		BX.ajax.post(
			url,
			{
				ajax_action: 'Y'
			},
			BX.proxy(this.deleteResult, this)
		);
	};

	CompareClass.prototype.deleteResult = function(result)
	{
		BX.closeWait();
		BX.onCustomEvent('OnCompareChange');
		BX(this.wrapObjId).innerHTML = result;

		this.refreshTabs();
	};

	CompareClass.prototype.refreshTabs = function()
	{
		var tab = $('#tabs .tabs-items > div');
		tab.hide().filter(':first').show();

		// Клики по вкладкам.
		$('#tabs .tabs-nav a').click(function () {
			tab.hide();
			tab.filter(this.hash).show();
			$('#tabs .tabs-nav a').removeClass('active');
			$(this).addClass('active');
			return false;
		}).filter(':first').click();

		// Клики по якорным ссылкам.
		$('.tabs-target').click(function () {
			$('#tabs .tabs-nav a[href=' + $(this).attr('href') + ']').click();
		});
	}

	return CompareClass;
})();

/** Табы для товаров */
$(function () {
	/*var tab = $('#tabs .tabs-items > div');
	tab.hide().filter(':first').show();

	// Клики по вкладкам.
	$('#tabs .tabs-nav a').click(function () {
		tab.hide();
		tab.filter(this.hash).show();
		$('#tabs .tabs-nav a').removeClass('active');
		$(this).addClass('active');
		return false;
	}).filter(':first').click();

	// Клики по якорным ссылкам.
	$('.tabs-target').click(function () {
		$('#tabs .tabs-nav a[href=' + $(this).attr('href') + ']').click();
	});

	// Отрытие вкладки из хеша URL
	if (window.location.hash) {
		$('#tabs-nav a[href=' + window.location.hash + ']').click();
		window.scrollTo(0, $("#".window.location.hash).offset().top);
	}*/
});