class ConfirmarExclusao extends HTMLElement {
    connectedCallback() {
        const modal = new bootstrap.Modal(this);
        const nomeEl = this.querySelector('#modalExcluirNome');
        const footer = this.querySelector('#modalExcluirFooter');
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

        this.addEventListener('show.bs.modal', function (event) {
            const gatilho = event.relatedTarget;
            nomeEl.textContent = gatilho.getAttribute('data-nome');
            formAlvo = document.getElementById(gatilho.getAttribute('data-form'));
        });
    }
}

customElements.define('confirmar-exclusao', ConfirmarExclusao);
