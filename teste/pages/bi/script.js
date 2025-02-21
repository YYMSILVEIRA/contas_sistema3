class NucleoBI {
    constructor() {
        this.tabelasSelecionadas = [];
        this.indicadoresSelecionados = [];
        this.filtrosSelecionados = [];
        this.groupBy = [];
        this.orderBy = [];
    }

    // Adiciona uma tabela à consulta
    adicionarTabela(tabela) {
        console.log("1- Tabela chamada dentro do método da classe NucleoBI: " + tabela + " tabela.nome-->" + tabela.nome);
        if (!this.tabelasSelecionadas.some(t => t.nome === tabela.nome)) {
            console.log("2- Tabela chamada dentro do método da classe NucleoBI: " + tabela);
            this.tabelasSelecionadas.push(tabela);
        }
    }

    // Adiciona um indicador à consulta
    adicionarIndicador(indicador) {
        // Verifica se a tabela foi informada
        if (!indicador.tabela) {
            throw new Error("Tabela não informada para o indicador.");
        }

        // Verifica se o indicador já foi adicionado
        if (!this.indicadoresSelecionados.some(ind =>
            ind.formula === indicador.formula && ind.tabela === indicador.tabela
        )) {
            // Adiciona o indicador à lista de indicadores selecionados
            this.indicadoresSelecionados.push(indicador);

            // Adiciona a tabela à lista de tabelas selecionadas (se ainda não estiver lá)
            if (!this.tabelasSelecionadas.some(tab => tab.nome === indicador.tabela)) {
                this.tabelasSelecionadas.push({ nome: indicador.tabela });
                console.log("Tabela adicionada:", indicador.tabela);
            }
        } else {
            console.log("Indicador duplicado. Não foi adicionado:", indicador);
        }
    }

    // Adiciona um filtro à consulta
    adicionarFiltro(filtro) {
        if (!this.filtrosSelecionados.includes(filtro)) {
            this.filtrosSelecionados.push(filtro);
        }
    }

    // Adiciona um campo para GROUP BY
    adicionarGroupBy(campo) {
        if (!this.groupBy.includes(campo)) {
            this.groupBy.push(campo);
        }
    }

    // Adiciona um campo para ORDER BY
    adicionarOrderBy(campo, direcao = "ASC") {
        this.orderBy.push({ campo, direcao });
    }

    // Monta a consulta SQL
    async montarConsulta() {
    if (this.tabelasSelecionadas.length === 0) {
        throw new Error("Nenhuma tabela selecionada.");
    }

    // Seleção dos indicadores (qualificados com o nome da tabela)
    const campos = this.indicadoresSelecionados.map(ind => {
        if (!ind.tabela) {
            throw new Error(`Tabela não definida para o indicador: ${ind.formula}`);
        }
        return `${ind.tabela}.${ind.formula}`;
    }).join(",\n    ");

    // Tabela principal (usamos a primeira tabela selecionada como padrão)
    const tabelaPrincipal = this.tabelasSelecionadas[0].nome;

    // Monta os JOINs dinamicamente
    const joinsPromises = this.tabelasSelecionadas
        .slice(1) // Ignora a primeira tabela (já é a tabela principal)
        .map(async tab => {
            try {
                console.log("tab.nome->" + tab.nome);
                console.log("tabelaPrincipal->" + tabelaPrincipal);

                // Faz a requisição fetch e aguarda a resposta
                const response = await fetch("ligacoes.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ tabPrincipal: tabelaPrincipal, tabelaNome: tab.nome })
                });

                if (!response.ok) {
                    throw new Error(`Erro na requisição: ${response.statusText}`);
                }

                // Converte a resposta para JSON
                const data = await response.json();

                // Verifica se o campo 'ligacao' existe e é uma string
                if (!data[0] || typeof data[0].ligacao !== "string") {
                    throw new Error("Formato de resposta inválido: campo 'ligacao' não encontrado ou não é uma string.");
                }

                // Retorna a ligação (join) obtida
                return data[0].ligacao;
            } catch (error) {
                console.error("Erro ao buscar ligação:", error);
                return ""; // Retorna uma string vazia em caso de erro
            }
        });

    // Aguarda todas as promessas de JOIN serem resolvidas
    const joins = await Promise.all(joinsPromises);

    // Verifica se 'joins' é um array
    if (!Array.isArray(joins)) {
        throw new Error("Erro ao montar os JOINs: resultado não é um array.");
    }

    // Junta todos os JOINs em uma única string
    const joinsString = joins.join("\n    ");

    // Monta a consulta SQL completa
    const consulta = `
        SELECT
            ${campos}
        FROM
            ${tabelaPrincipal}
            ${joinsString}
    `;

    return consulta;
}
}

// Cria uma única instância global do NucleoBI
const nucleoBI = new NucleoBI();

// Função para configurar o menu de contexto
function setupContextMenu(card, indicadorDescricao, tabela, coluna) {
    // Cria o menu de contexto
    const contextMenu = document.createElement("div");
    contextMenu.className = "context-menu";
    contextMenu.innerHTML = `
        <ul>
            <li class="context-menu-item" data-action="excluir" style="border: 1px solid #d2d2d2;border-radius: 3px;cursor: pointer;display: block;margin: 2px;padding: 2px 6px;width: 100%;">Excluir</li>
        </ul>
    `;
    document.body.appendChild(contextMenu);

    // Exibe o menu de contexto ao clicar com o botão direito
    card.addEventListener("contextmenu", (e) => {
        e.preventDefault(); // Impede o menu de contexto padrão do navegador
        contextMenu.style.display = "block";
        contextMenu.style.left = `${e.pageX}px`; // Posiciona o menu no local do clique
        contextMenu.style.top = `${e.pageY}px`;
    });

    // Fecha o menu ao clicar em qualquer lugar do site
    document.addEventListener("click", (e) => {
        if (contextMenu.style.display === "block" && !contextMenu.contains(e.target)) {
            contextMenu.style.display = "none";
        }
    });

    // Adiciona ações ao menu de contexto
    contextMenu.querySelectorAll(".context-menu-item").forEach((item) => {
        item.addEventListener("click", async () => { // Adicionado async aqui
            if (item.getAttribute("data-action") === "excluir") {
                card.remove(); // Remove o card

                // Remove o indicador e a tabela do NucleoBI
                nucleoBI.indicadoresSelecionados = nucleoBI.indicadoresSelecionados.filter(ind => ind.formula !== coluna);
                if (nucleoBI.indicadoresSelecionados.every(ind => !ind.formula.startsWith(tabela))) {
                    nucleoBI.tabelasSelecionadas = nucleoBI.tabelasSelecionadas.filter(tab => tab.nome !== tabela);
                }

                // Atualiza a consulta SQL
                try {
                    const consulta = await nucleoBI.montarConsulta(); // Usando await aqui
                    document.getElementById("consulta-sql").textContent = consulta; // Exibe a consulta na tela
                } catch (error) {
                    console.error("Erro ao montar a consulta:", error.message);
                }

                toastr.warning(`Indicador "${indicadorDescricao}" removido do painel de análise.`);
            }
            contextMenu.style.display = "none"; // Fecha o menu após a ação
        });
    });
}

$(document).ready(function () {
    // Configuração do Toastr para notificações
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
        };
        toastr.success("Bem-vindo ao Sistema de BI!");
    } else {
        console.error("Toastr não está carregado. Notificações não serão exibidas.");
    }

    // Configuração do Dragula para arrastar e soltar itens
    const draggableContainers = [
        document.querySelector(".bi-painel-lateral-conteudo"), // Painel lateral (itens arrastáveis)
        document.querySelector(".bi-analise-conteudo"), // Área de análise (onde os itens serão soltos)
    ];

    const drake = dragula(draggableContainers, {
        copy: (el, source) => {
            // Copia os itens apenas se forem arrastados do painel lateral
            return source === document.querySelector(".bi-painel-lateral-conteudo");
        },
        accepts: (el, target) => {
            // Restringe onde os itens podem ser soltos (apenas na área de análise)
            return target === document.querySelector(".bi-analise-conteudo");
        },
    });

    // Evento quando um item é solto no painel de análise
    drake.on("drop", async (el, target) => { // Adicionado async aqui
        if (target === document.querySelector(".bi-analise-conteudo")) {
            const descricaoElement = el.querySelector(".bi-painel-lateral-indicador-descricao");
            if (descricaoElement) {
                const indicadorDescricao = descricaoElement.textContent;
                const tabela = descricaoElement.closest('.bi-painel-lateral-indicador').getAttribute('data-tab');
                const coluna = descricaoElement.closest('.bi-painel-lateral-indicador').getAttribute('data-col');

                // Adiciona o indicador ao NucleoBI
                nucleoBI.adicionarIndicador({ formula: coluna, tabela: tabela });

                // Monta a consulta SQL
                try {
                    const consulta = await nucleoBI.montarConsulta(); // Usando await aqui
                    document.getElementById("consulta-sql").textContent = consulta; // Exibe a consulta na tela
                } catch (error) {
                    console.error("Erro ao montar a consulta:", error.message);
                }

                // Cria um novo card para o indicador
                const newCard = document.createElement("div");
                newCard.className = "bi-grid draggable-card";
                newCard.innerHTML = `
                    <div class="bi-grid-header">
                        <div class="bi-grid-header-cell">${indicadorDescricao}</div>
                    </div>
                    <div class="bi-grid-body">
                        <div class="bi-grid-body-cell">1.000.000,00</div>
                    </div>
                `;

                // Adiciona o card ao conteúdo de análise
                target.appendChild(newCard);

                // Remove o item clonado do painel de análise (se houver)
                if (el.parentElement === target) {
                    target.removeChild(el);
                }

                // Configura o Interact.js para mover o novo card
                setupDraggableCard(newCard);

                // Adiciona o menu de contexto ao card
                setupContextMenu(newCard, indicadorDescricao, tabela, coluna);
            } else {
                console.error("Elemento .bi-painel-lateral-indicador-descricao não encontrado.");
            }
        }
    });

    // Função para configurar o movimento dos cards
    function setupDraggableCard(card) {
        interact(card).draggable({
            inertia: true, // Permite movimento suave
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: "parent", // Restringe o movimento ao contêiner pai
                    endOnly: true,
                }),
            ],
            autoScroll: true,
            listeners: {
                move: dragMoveListener, // Função que move o card
            },
        });

        // Função para mover o card
        function dragMoveListener(event) {
            const target = event.target;
            const x = (parseFloat(target.getAttribute("data-x")) || 0) + event.dx;
            const y = (parseFloat(target.getAttribute("data-y")) || 0) + event.dy;

            // Atualiza a posição do card
            target.style.transform = `translate(${x}px, ${y}px)`;
            target.setAttribute("data-x", x);
            target.setAttribute("data-y", y);
        }
    }

    // Carrega os indicadores dinamicamente
    loadIndicadores();
});

// Função para carregar indicadores dinamicamente
async function loadIndicadores() {
    Indicadores = [];
    try {
        const resposta = await fetch('cubos_indicadores.php');
        if (!resposta.ok) {
            throw new Error('Erro ao buscar indicadores');
        }

        // Converte a resposta para JSON
        const Indicadores = await resposta.json();

        const divContainer = document.querySelector('.bi-painel-lateral-conteudo');
        divContainer.innerHTML = ""; // Limpa o conteúdo existente

        // Adiciona cada indicador ao painel lateral
        Indicadores.forEach(indicador => {
            const div = document.createElement('div');
            div.title = indicador.descricaoIndicador;
            div.className = 'bi-painel-lateral-indicador bi-indicador-drop-filtros draggable holder-btn-flutuante';
            div.innerHTML = `
                <i class="bi-painel-lateral-indicador-icone fas fa-calculator fas fa-bars"></i>
                <span class="bi-painel-lateral-indicador-descricao" style="border: 1px solid #d2d2d2;border-radius: 3px;cursor: pointer;display: block;margin: 2px;padding: 2px 6px;width: 100%;">${indicador.nomeIndicador} (${indicador.descricaoTabela})</span>
                <i class="btn-flutuante indicador-btn-opcoes fas fa-bars"></i>
            `;

            div.setAttribute('data-tab', indicador.nomeTabela);
            div.setAttribute('data-col', indicador.nomeIndicador);
            divContainer.appendChild(div);

            // Configura o menu de contexto para o novo indicador
            setupContextMenu(div, indicador.nomeIndicador, indicador.nomeTabela, indicador.nomeIndicador);
        });
    } catch (erro) {
        console.error('Erro ao carregar indicadores:', erro);
        if (typeof toastr !== 'undefined') {
            toastr.error('Erro ao carregar indicadores. Tente novamente mais tarde.');
        }
    }
}