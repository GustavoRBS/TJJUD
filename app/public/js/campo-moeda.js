document.addEventListener('DOMContentLoaded', function () {
    const campo = document.getElementById('livro_valor');
    if (!campo) {
        return;
    }

    const digitosParaCentavos = (valor) => {
        const digitos = valor.replace(/\D/g, '');
        return digitos === '' ? 0 : parseInt(digitos, 10);
    };

    const paraExibicao = (centavos) => {
        const [inteiro, decimal] = (centavos / 100).toFixed(2).split('.');
        const comMilhar = inteiro.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return `${comMilhar},${decimal}`;
    };

    if (campo.value.trim() !== '') {
        campo.value = paraExibicao(digitosParaCentavos(campo.value));
    }

    campo.addEventListener('input', function () {
        campo.value = paraExibicao(digitosParaCentavos(campo.value));
    });

    campo.closest('form').addEventListener('submit', function () {
        campo.value = (digitosParaCentavos(campo.value) / 100).toFixed(2);
    });
});
