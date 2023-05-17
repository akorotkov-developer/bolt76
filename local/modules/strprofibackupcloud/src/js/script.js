BX.ready(function() {
    var isStarted = false;

    /**
     * Создать резервную копию на внешнем диске
     */
    BX.bind(BX('create_copy'), 'click', function() {
        if (!isStarted) {
            isStarted = true;

            $('.copy_progress').show();

            console.log('Тут 1');
            BX.ajax({
                url: '/local/modules/' + admin_module_name + '/tools/run_upload.php',
                data: {
                    'action': 'create_copy',
                    'disk_type': document.getElementById('disk_type').value
                },
                method: 'POST',
                dataType: 'json',
                async: true,
                onsuccess: function(data) {
                    console.log(data, 'data');
                    checkExportStatus(data);
                },
                onfailure: function() {

                }
            });
        }
    });

    /**
     * Старт переноса резервных копий
     */
    BX.bind(BX('start_copy_backup'), 'click', function() {
        if (!isStarted) {
            $('.success_message_backup').text('Перенос резервных копий на Яндекс.Диск запущен');

            isStarted = true;

            BX.ajax({
                url: '/local/modules/' + admin_module_name + '/tools/run_upload.php',
                data: {
                    'action': 'start_upload',
                    'disk_type': document.getElementById('disk_type').value
                },
                method: 'POST',
                dataType: 'json',
                async: true,
                onsuccess: function(data) {
                    console.log(data, 'data');
                },
                onfailure: function() {

                }
           });
        }
    });

    /**
     * Старт резервного копирования
     */
    /*BX.bind(BX('start_reserv_copy'), 'click', function() {
        var queryString = '?lang=ru&process=Y&action=start&dump_bucket_id=0&dump_max_exec_time=20&dump_max_exec_time_sleep=1&dump_archive_size_limit=100&dump_integrity_check=Y&dump_file_public=Y&dump_file_kernel=Y&skip_mask=Y&arMask[]=%2Fimport%2F.cache&arMask[]=%2Fbitrix%2Fbackup&arMask[]=%2Fupload%2Fresize_cache&arMask[]=%2Fupload%2Ftmp&arMask[]=%2Fimport%2Fimg&arMask[]=&max_file_size=0&dump_base=Y&sessid=1e0b402750f2452b7eced6d4ee9e83f4';
        queryString += '&' + bitrix_sesion_id;

        AjaxSend('dump.php', queryString);
    });*/
    BX.bind(BX('start_reserv_copy'), 'click', function() {
        $('.success_message_backup').text('Процесс резервного копирования и переноса на внешний диск запущен');

        BX.ajax({
            url: '/local/modules/' + admin_module_name + '/tools/run_upload.php',
            data: {
                'action': 'rezerv_copy',
            },
            method: 'POST',
            dataType: 'json',
            async: true,
            onsuccess: function(data) {

            },
            onfailure: function() {
            }
        });
    });

    /**
     * Проверить текущий статус переноса резервных копий
     */
    /*setInterval(checkExportStatus, 10000);*/
});

/**
 * Проверить текущий статус переноса резервных копий
 */
function checkExportStatus(rowId)
{
    console.log(rowId, 'rowId');
    console.log(admin_module_name, 'admin_module_name');

    BX.ajax({
        url: '/local/modules/strprofibackupcloud/tools/check_status.php?rowid=' . rowId,
        data: {},
        method: 'POST',
        dataType: 'json',
        async: true,
        onsuccess: function(data) {
            console.log(Number(data));
            if (Number(data) > 0) {
                $('#progress_for_cur_copy').css('width', data + '%');
            }
        },
        onfailure: function() {

        }
    });
}

var counter_started = false;
var counter_sec = 0;
var stop;

// Старт счетчика
function StartCounter() {
    counter_started = true;
}

// Стоп счетчика
function StopCounter(result) {
    if (result) {
        var regs;
        if (regs = /<!--([0-9]+)-->/.exec(result)) {
            counter_sec = regs[1];
        }
    }
}

function EndDump() {
    stop = true;
}

/**
 * Отправка Ajax запроса на старт резервного копирования
 * @param url
 * @param data
 * @constructor
 */
function AjaxSend(url, data) {
    stop = false;

    StartCounter();
    CHttpRequest.Action = function (result) {

        StopCounter(result);

        if (stop) {
            EndDump();
        }
    };

    if (data) {
        CHttpRequest.Post(url, data);
    } else {
        CHttpRequest.Send(url);
    }
}

/**
 * Получить размер таблиц БД
 */
function getTableSize() {
    AjaxSend('?ajax_mode=Y&action=get_table_size');
}

var numRows=0;
function AddTableRow()
{
    var oTable = BX('skip_mask_table');
    numRows = oTable.rows.length;
    var oRow = oTable.insertRow(-1);
    var oCell = oRow.insertCell(0);
    oCell.innerHTML = '<input type="text" name="arMask[]" id="mnu_FILES_' + numRows  +'" size=30><input type="button" id="mnu_FILES_btn_' + numRows  + '" value="..." onclick="showMenu(this, '+ numRows  +')">';
}