(function () {
    'use strict';

    var el = {
        kind: document.getElementById('el-001'),
        body: document.getElementById('el-002'),
        viewport: document.getElementById('el-003'),
        scheme: document.getElementById('el-004'),
        subject: document.getElementById('el-005'),
        frame: document.getElementById('el-006'),
        text: document.getElementById('el-007'),
        address: document.getElementById('el-008'),
        send: document.getElementById('el-009'),
        result: document.getElementById('el-010')
    };

    var state = 'initial';

    function addressIsValid() {
        return /.+@.+\..+/.test(el.address.value.trim());
    }

    function applyState(next) {
        state = next;
        document.getElementById('scr-001').dataset.state = next;

        var busy = next === 'loading' || next === 'sending' || next === 'error';
        el.send.disabled = busy || !addressIsValid();
    }

    function query() {
        return {
            kind: el.kind.value,
            body: el.body.value,
            scheme: el.scheme.value
        };
    }

    function url(path, params) {
        return path + '?' + encodeURIComponent('kind') + '=' + encodeURIComponent(params.kind)
            + '&' + encodeURIComponent('body') + '=' + encodeURIComponent(params.body)
            + '&' + encodeURIComponent('scheme') + '=' + encodeURIComponent(params.scheme);
    }

    function showError(errId) {
        el.subject.textContent = '';
        el.text.textContent = '';
        el.frame.removeAttribute('src');
        el.frame.dataset.error = errId;
        applyState('error');
    }

    function update() {
        var params = query();

        el.frame.style.width = el.viewport.value === 'mobile' ? '375px' : '100%';
        el.result.hidden = true;
        el.frame.removeAttribute('data-error');
        applyState('loading');

        fetch(url('/meta', params))
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('meta ' + response.status);
                }

                return response.json();
            })
            .then(function (meta) {
                el.subject.textContent = meta.subject;
                el.text.textContent = meta.text;
                el.frame.src = url('/preview', params);
                applyState('populated');
            })
            .catch(function () {
                showError('ERR-001');
            });
    }

    ['kind', 'body', 'viewport', 'scheme'].forEach(function (key) {
        el[key].addEventListener('change', update);
    });

    el.address.addEventListener('input', function () {
        if (state !== 'loading' && state !== 'sending' && state !== 'error') {
            applyState(state);
        }
    });

    el.frame.addEventListener('load', function () {
        if (state === 'populated') {
            return;
        }
    });

    applyState('initial');
    update();
})();
