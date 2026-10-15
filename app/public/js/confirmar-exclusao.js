document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalExcluir');
    if (!modalEl) {
        return;
    }

    const modal = new bootstrap.Modal(modalEl);
    const nomeEl = document.getElementById('modalExcluirNome');
    const footer = document.getElementById('modalExcluirFooter');
    let formAlvo = null;

    const botaoConfirmar = document.createElement('button');
    botaoConfirmar.type = 'button';
    botaoConfirmar.className = 'btn btn-danger';
    botaoConfirmar.textContent = 'Excluir';
    botaoConfirmar.addEventListener('click', function () {
        if (formAlvo) {
            formAlvo.submit();
        }
    });
    footer.appendChild(botaoConfirmar);

    modalEl.addEventListener('show.bs.modal', function (event) {
        const gatilho = event.relatedTarget;
        nomeEl.textContent = gatilho.getAttribute('data-nome');
        formAlvo = document.getElementById(gatilho.getAttribute('data-form'));
    });
});
