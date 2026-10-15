document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select[multiple]').forEach(function (campo) {
        new TomSelect(campo, {
            plugins: ['remove_button'],
            placeholder: 'Selecione...',
            noResultsText: 'Nenhum resultado encontrado',
            noChoicesText: 'Nenhuma opção disponível',
            loadingText: 'Carregando...',
            addItemText: function (valor) {
                return 'Adicionar "' + valor + '"';
            },
        });
    });
});
