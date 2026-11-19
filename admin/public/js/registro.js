document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-registro]');
    if (!form) return;
    const senha = form.querySelector('#senha');
    const confirmar = form.querySelector('#confirmar_senha');
    const mensagem = form.querySelector('#erro-senha');
    const validar = () => {
        const diferente = confirmar.value !== '' && senha.value !== confirmar.value;
        confirmar.setCustomValidity(diferente ? 'As senhas não conferem.' : '');
        mensagem.textContent = diferente ? 'As senhas não conferem.' : '';
    };
    senha.addEventListener('input', validar);
    confirmar.addEventListener('input', validar);
    form.addEventListener('submit', validar);
});
