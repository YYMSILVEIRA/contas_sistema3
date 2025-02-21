$(document).ready(function() {
    // Configuração do Toastr para notificações
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    toastr.success('Bem-vindo ao Sistema de BI!');

    // Configuração do Dragula para arrastar e soltar itens
    const draggableContainers = [
        document.querySelector('.bi-painel-lateral-conteudo'), // Painel lateral (itens arrastáveis)
        document.querySelector('.bi-analise-conteudo')         // Área de análise (onde os itens serão soltos)
    ];

    const drake = dragula(draggableContainers, {
        copy: (el, source) => {
            // Copia os itens apenas se forem arrastados do painel lateral
            return source === document.querySelector('.bi-painel-lateral-conteudo');
        },
        accepts: (el, target) => {
            // Restringe onde os itens podem ser soltos (apenas na área de análise)
            return target === document.querySelector('.bi-analise-conteudo');
        }
    });

    // Função para carregar tabelas e indicadores no painel lateral
    function carregarTabelasIndicadores() {
        fetch('buscar_tabelas_indicadores.php') // URL do script PHP
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    toastr.error(data.error); // Exibe erro se houver
                } else {
                    const painelLateral = document.querySelector('.bi-painel-lateral-conteudo');
                    painelLateral.innerHTML = ''; // Limpa o conteúdo atual

                    // Adiciona cada tabela e seus indicadores ao painel lateral
                    data.forEach(function(tabela) {
                        const tabelaElement = document.createElement('div');
                        tabelaElement.className = 'bi-painel-lateral-tabela';
                        tabelaElement.innerHTML = `<br>
                            <div class="bi-painel-lateral-tabela-header">
                                <i class="fas fa-table"></i>
                                <span style="border: 1px solid black; border-radius:2px;"><b>${tabela.nome_tabela}</b></span>
                            </div>
                            <div class="bi-painel-lateral-tabela-indicadores">
                                ${tabela.indicadores.map(indicador => `
                                    <div class="bi-painel-lateral-indicador bi-indicador-drop-filtros draggable holder-btn-flutuante" data-id="${indicador.id}">
                                        <i class="bi-painel-lateral-indicador-icone fas fa-calculator"></i>
                                        <span style="border: 1px solid black; border-radius:2px;" class="bi-painel-lateral-indicador-descricao">  &emsp; ${indicador.nome_indicador} &emsp; </span>
                                        <i class="btn-flutuante indicador-btn-opcoes fas fa-bars"></i>
                                    </div>
                                `).join('')}
                            </div>
                        `;

                        painelLateral.appendChild(tabelaElement);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao buscar tabelas e indicadores:', error);
                toastr.error('Erro ao carregar tabelas e indicadores.');
            });
    }

    // Carrega as tabelas e indicadores no painel lateral ao iniciar
    carregarTabelasIndicadores();

    // Evento quando um item é solto no painel de análise
    drake.on('drop', (el, target) => {
        if (target === document.querySelector('.bi-analise-conteudo')) {
            const descricaoElement = el.querySelector('.bi-painel-lateral-indicador-descricao');
            if (descricaoElement) {
                const indicadorDescricao = descricaoElement.textContent;
                const indicadorId = el.getAttribute('data-id'); // Obtém o ID do indicador

                // Adiciona o indicador como uma coluna na tabela
                adicionarColunaTabela(indicadorId, indicadorDescricao);
            } else {
                console.error('Elemento .bi-painel-lateral-indicador-descricao não encontrado.');
            }
        }
    });

    // Função para adicionar uma coluna na tabela
    function adicionarColunaTabela(indicadorId, indicadorDescricao) {
        const tableHeader = document.querySelector('#table-header');
        const tableBody = document.querySelector('#table-body');

        // Adiciona o cabeçalho da coluna
        const th = document.createElement('th');
        th.textContent = indicadorDescricao;
        th.setAttribute('data-id', indicadorId); // Armazena o ID do indicador
        tableHeader.appendChild(th);

        // Busca os dados do indicador e preenche as linhas
        console.log(indicadorId);
        fetch(`buscar_dados_indicador.php?id=${indicadorId}`) // URL do script PHP
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    toastr.error(data.error); // Exibe erro se houver
                } else {
                    // Adiciona os dados nas linhas da tabela
                    data.forEach((item, index) => {
                        let row = tableBody.querySelector(`tr[data-index="${index}"]`);
                        if (!row) {
                            row = document.createElement('tr');
                            row.setAttribute('data-index', index);
                            tableBody.appendChild(row);
                        }

                        const td = document.createElement('td');
                        td.textContent = item.valor; // Exibe o valor do indicador
                        row.appendChild(td);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao buscar dados do indicador:', error);
                toastr.error('Erro ao carregar dados do indicador.');
            });
    }
});