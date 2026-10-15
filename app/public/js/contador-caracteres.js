document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[maxlength]').forEach(function (campo) {
        const contador = document.createElement('small');
        contador.className = 'form-text text-muted';
        campo.after(contador);

        const atualizar = function () {
            contador.textContent = `${campo.value.length}/${campo.maxLength} caracteres`;
        };
        campo.addEventListener('input', atualizar);
        atualizar();
    });
});
