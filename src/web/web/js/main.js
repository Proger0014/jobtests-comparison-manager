$(function () {
    const select = $('#organization_select');

    select.select2();

    select.on('select2:select', (e) => {
        setToUrl({ orgId: e.params.data.id });

        $.pjax.defaults.timeout = false;
        $.pjax.reload({ container: '#pjax-default' });
    });

    const existsData = select.select2('data')[0];
    select.trigger({
        type: 'select2:select',
        params: {
            data: existsData
        }
    });
});

$(function() {
    const input = $('#occurrence');
    const submitButton = $('#occurrence_submit');

    const { threshold } = extractFromUrl(['threshold']);

    input.val(threshold);

    submitButton.on('click', (e) => {
        let value = input.val();
        value = value.toString().replace('\%', '');
        setToUrl({ threshold: value });
    });
});

function ajax(params) {
    const { orgId } = extractFromUrl(['orgId']);

    return {
        q: params.term,
        orgId: orgId,
        page: params.page || 1,
        pageSize: 10
    }
}

function selectAddressClient(containerId, text, id) {
    const select2 = $(containerId);

    select2.select2();

    const option = new Option(text, id, true, true);
    select2.append(option).trigger('change');
}

function unbindButtonAction(containerId) {
    const select2 = $(containerId);

    select2.select2();

    select2.on('select2:clear', (e) => {
        const id = e.params.data[0].id;

        $.ajax({
            url: `${id}/unbind`,
            method: 'POST',
        })
        .done(() => {
            $.pjax.defaults.timeout = false;
            $.pjax.reload({ container: '#pjax-default' })
        });
    });
}

function bindButtonAction(srcId, containerId) {
    const select2 = $(containerId);

    select2.select2();

    select2.on('select2:select', (e) => {
        const id = e.params.data.id;

        $.ajax({
            url: `${id}/bind`,
            method: 'POST',
            dataType: 'json',
            data: { srcId: srcId }
        })
        .done(() => {
            $.pjax.defaults.timeout = false;
            $.pjax.reload({ container: '#pjax-default' })
        })
    });
}

function ajaxProcessResult(data, params) {
    params.page = params.page || 1;

    const mapped = Array.from(data.entities).map(e => {
        return {
            id: e.id,
            text: e.address
        }
    })

    return {
        results: mapped,
        pagination: {
            more: (params.page * 10) < data.total
        }
    }
}

function templateResult(item) {
    return item.text;
}

function templateSelection(item) {
    return item.text;
}

function extractFromUrl(keys) {
    let result = { };

    const url = new URL(window.location.href);

    keys.forEach(k => {
        result[k] = url.searchParams.get(k);
    });

    return result;
}

function setToUrl(obj) {
    const url = new URL(window.location.href);

    Object.keys(obj).forEach(k => {
        url.searchParams.set(k, obj[k]);
    });

    window.history.pushState(null, document.title, url);
}

$(function () {
    const button = $('#auto_run_dialog_btn');

    const { orgId } = extractFromUrl(['orgId']);

    button.on('click', (e) => {
        $.ajax({
            url: 'find-manual-binds-info',
            method: 'POST',
            dataType: 'json',
            data: {
                orgId: orgId
            }
        })
        .done((data) => {
            if (data.count > 0) {
                $('#modal-container').html(`
                <div id="modal-target" class="modal fade" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Пересопоставить ручные записи</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p>Найдено ${data.count} адресов, которые уже сопоставленые вручную. Желаете их пересопоставить или оставить как есть?</p>
                      </div>
                      <div class="modal-footer">
                        <button id="find-manual-info-0" type="button" class="btn btn-primary">Пересопоставить</button>
                        <button id="find-manual-info-1" type="button" class="btn btn-primary">Оставить</button>
                      </div>
                    </div>
                  </div>
                </div>`);

                const modal = new bootstrap.Modal('#modal-target');

                modal.show();

                $('#find-manual-info-0').on('click', (e) => {
                    modal.dispose();
                    autoRun(orgId, true);
                });

                $('#find-manual-info-1').on('click', (e) => {
                    modal.dispose();
                    autoRun(orgId, false);
                });
            } else {
                autoRun(orgId, false);
            }
        });
    });
});

function autoRun(orgId, rebindManual) {
    const { threshold } = extractFromUrl(['threshold']);

    $.ajax({
        url: 'auto-run',
        method: 'POST',
        dataType: 'json',
        data: {
            orgId: orgId,
            threshold: threshold,
            rebindManual: rebindManual
        }
    })
    .done((data) => {
        $.pjax.defaults.timeout = false;
        $.pjax.reload({ container: '#pjax-default' });

        $('#modal-container').html(`
                <div id="modal-target" class="modal fade" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Результат автосопоставления</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <ul>
                            <li>Обработано строк: ${data.processed}</li>
                            <li>Найдено подходящих записей: ${data.matched}</li>
                            <li>Автосопоставлено записей: ${data.auto}</li>
                            <li>Пропущено записей: ${data.skipped}</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>`);

        const modal = new bootstrap.Modal('#modal-target');

        modal.show();
    });
}

function loadAddressSubmitOverride(form) {
    $('form#load-address-form').on('submit', (e) => {
        const { orgId } = extractFromUrl(['orgId']);

        $(`[name="${form}\[orgId\]"]`).first().val(orgId);
    })
}
