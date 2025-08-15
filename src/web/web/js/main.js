$(function () {
    const select = $('#organization_select');

    select.select2();

    select.on('select2:select', (e) => {
        setToUrl({ orgId: e.params.data.id })
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
    const { orgId } = extractFromUrl(['orgId', 'page', 'pageSize']);

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