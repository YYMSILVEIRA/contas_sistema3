// Seleciona o botão e o local onde as informações serão exibidas
const botaoBuscar = document.getElementById('buscarUsuario');
const usuarioInfo = document.getElementById('usuarioInfo');

// Função para buscar um usuário aleatório
async function buscarUsuario() {
    try {
        // Faz a requisição à API
        const resposta = await fetch('https://jsonplaceholder.typicode.com/users/2'); // ID 1 para exemplo
        if (!resposta.ok) {
            throw new Error('Erro ao buscar usuário');
        }

        // Converte a resposta para JSON
        const usuario = await resposta.json();

        // Exibe as informações do usuário na página
        usuarioInfo.innerHTML = `
            <p><strong>Nome:</strong> ${usuario.name}</p>
            <p><strong>Email:</strong> ${usuario.email}</p>
            <p><strong>Telefone:</strong> ${usuario.phone}</p>
            <p><strong>Empresa:</strong> ${usuario.company.name}</p>
        `;
    } catch (erro) {
        // Exibe uma mensagem de erro se algo der errado
        usuarioInfo.innerHTML = `<p style="color: red;">Erro: ${erro.message}</p>`;
    }
}

// Adiciona um evento de clique ao botão
botaoBuscar.addEventListener('click', buscarUsuario);