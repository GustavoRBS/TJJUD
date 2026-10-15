document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn[title]').forEach(function (botao) {
        new bootstrap.Tooltip(botao);
    });
});
