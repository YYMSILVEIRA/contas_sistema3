document.getElementById('buscarUsuarios').addEventListener('click', async () => {
    try {
        // Faz uma requisição AJAX para buscar os usuários
        const resposta = await fetch('dados.php');
        if (!resposta.ok) {
            throw new Error('Erro ao buscar usuários');
        }

        // Converte a resposta para JSON
        const usuarios = await resposta.json();

        // Exibe os usuários na página
        const resultadoDiv = document.getElementById('resultado');
        resultadoDiv.innerHTML = ''; // Limpa o conteúdo anterior

        // Cria uma lista de usuários
        const listaUsuarios = document.createElement('ul');
        usuarios.forEach(usuario => {
            const itemLista = document.createElement('li');
            itemLista.innerHTML = `
                <strong>Nome:</strong> ${usuario.nome}<br>
                <strong>Idade:</strong> ${usuario.idade}<br>
                <strong>Cidade:</strong> ${usuario.cidade}
            `;
            listaUsuarios.appendChild(itemLista);
        });

        resultadoDiv.appendChild(listaUsuarios);
    } catch (erro) {
        // Exibe uma mensagem de erro se algo der errado
        console.error('Erro:', erro);
        document.getElementById('resultado').innerHTML = `<p style="color: red;">Erro: ${erro.message}</p>`;
    }
});