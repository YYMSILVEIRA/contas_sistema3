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
        // Verifica se a tabela já foi adicionada (usando o nome como identificador único)
        if (!this.tabelasSelecionadas.some(t => t.nome === tabela.nome)) {
            this.tabelasSelecionadas.push(tabela);
        }
    }

    // Adiciona um indicador à consulta
    adicionarIndicador(indicador) {
        // Limpa os indicadores anteriores e adiciona apenas o novo
        this.indicadoresSelecionados = [indicador];
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
    montarConsulta() {
        if (this.tabelasSelecionadas.length === 0) {
            throw new Error("Nenhuma tabela selecionada.");
        }

        // Seleção dos indicadores (qualificando as colunas com o nome da tabela)
        const campos = this.indicadoresSelecionados.map(ind => {
            // Verifica se o indicador já está qualificado com o nome da tabela
            if (!ind.formula.includes(".")) {
                // Se não estiver qualificado, usa a primeira tabela selecionada como padrão
                return `${this.tabelasSelecionadas[0].nome}.${ind.formula}`;
            }
            return ind.formula;
        }).join(", ");

        // Tabela principal (apenas a primeira tabela selecionada)
        const tabelaPrincipal = this.tabelasSelecionadas[0].nome;

        // Monta os JOINs dinamicamente
        const joins = this.tabelasSelecionadas
            .flatMap(tab => (tab.joins || []).map(join => ({ ...join, tabelaOrigem: tab.nome }))) // Passa o nome da tabela de origem
            .filter((join, index, self) => {
                // Remove JOINs duplicados
                return index === self.findIndex(j => (
                    j.tabela === join.tabela &&
                    j.tabelaOrigem === join.tabelaOrigem &&
                    j.campoLocal === join.campoLocal &&
                    j.campoExterno === join.campoExterno
                ));
            })
            .map((join, index) => {
                // Adiciona aliases únicos para tabelas duplicadas
                const tabelaAlias = join.tabela === "situacoes" ? `situacoes_${index}` : join.tabela;
                return `JOIN ${join.tabela} AS ${tabelaAlias} ON (${join.tabelaOrigem}.${join.campoLocal} = ${tabelaAlias}.${join.campoExterno})`;
            })
            .join(" ");

        // Filtros (WHERE)
        const filtros = this.filtrosSelecionados.length > 0
            ? `WHERE ${this.filtrosSelecionados.map(fil => fil.condicao).join(" AND ")}`
            : "";

        // GROUP BY
        const groupBy = this.groupBy.length > 0
            ? `GROUP BY ${this.groupBy.join(", ")}`
            : "";

        // ORDER BY
        const orderBy = this.orderBy.length > 0
            ? `ORDER BY ${this.orderBy.map(ob => `${ob.campo} ${ob.direcao}`).join(", ")}`
            : "";

        // Monta a consulta SQL
        let consulta = `SELECT ${campos} FROM ${tabelaPrincipal} ${joins} ${filtros} ${groupBy} ${orderBy}`;

        // Remove espaços em branco desnecessários
        consulta = consulta.replace(/\s+/g, " ").trim();

        return consulta;
    }
}

// Cria uma única instância global do NucleoBI
const nucleoBI = new NucleoBI();

// Exemplo de tabelas
const tabelas = [
    {
        nome: "contas_pagar",
        joins: [
            {
                tabela: "cliente_fornecedor",
                campoLocal: "fornecedor",
                campoExterno: "nome",
            },
            {
                tabela: "categorias",
                campoLocal: "id_categoria",
                campoExterno: "id",
            },
            {
                tabela: "situacoes",
                campoLocal: "id_situacao",
                campoExterno: "id",
            }
        ],
    },
    {
        nome: "categorias",
        joins: [
            {
                tabela: "situacoes",
                campoLocal: "id_situacao",
                campoExterno: "id",
            },
               ],
    },
];

// Adiciona tabelas ao NucleoBI (sem duplicação)
tabelas.forEach(tabela => nucleoBI.adicionarTabela(tabela));

// Adiciona indicadores ao NucleoBI
nucleoBI.adicionarIndicador({ formula: "id" });
nucleoBI.adicionarIndicador({ formula: "valor" });
nucleoBI.adicionarIndicador({ formula: "fornecedor" });
nucleoBI.adicionarIndicador({ formula: "id_situacao" });
nucleoBI.adicionarIndicador({ formula: "data" });

// Monta a consulta SQL
try {
    const consulta = nucleoBI.montarConsulta();
    console.log("Consulta SQL:", consulta);
} catch (error) {
    console.error("Erro ao montar a consulta:", error.message);
}

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
        item.addEventListener("click", () => {
            if (item.getAttribute("data-action") === "excluir") {
                card.remove(); // Remove o card

                // Remove o indicador e a tabela do NucleoBI
                nucleoBI.indicadoresSelecionados = nucleoBI.indicadoresSelecionados.filter(ind => ind.formula !== coluna);
                if (nucleoBI.indicadoresSelecionados.every(ind => !ind.formula.startsWith(tabela))) {
                    nucleoBI.tabelasSelecionadas = nucleoBI.tabelasSelecionadas.filter(tab => tab.nome !== tabela);
                }

                // Atualiza a consulta SQL
                try {
                    const consulta = nucleoBI.montarConsulta();
                    console.log("Consulta SQL atualizada:", consulta);
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
    drake.on("drop", (el, target) => {
        if (target === document.querySelector(".bi-analise-conteudo")) {
            const descricaoElement = el.querySelector(".bi-painel-lateral-indicador-descricao");
            if (descricaoElement) {
                const indicadorDescricao = descricaoElement.textContent;

                // Extrai as informações da tabela e coluna
                const tabela = descricaoElement.closest('.bi-painel-lateral-indicador').getAttribute('data-tab');
                const coluna = descricaoElement.closest('.bi-painel-lateral-indicador').getAttribute('data-col');

                // Adiciona a tabela e o indicador ao NucleoBI
                nucleoBI.adicionarTabela({ nome: tabela }); // Adiciona a tabela
                nucleoBI.adicionarIndicador({ formula: coluna }); // Adiciona o indicador (usando a coluna como fórmula)

                // Monta a consulta SQL
                try {
                    const consulta = nucleoBI.montarConsulta();
                    console.log("Consulta SQL atualizada:", consulta);
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
            
            // Definindo os campos da tabela
            div.setAttribute('data-tab', indicador.nomeTabela);
            div.setAttribute('data-col', indicador.nomeIndicador);
            div.setAttribute('data-tab-col', indicador.nomeTabela + "." + indicador.nomeIndicador);

            div.title = indicador.descricaoIndicador;
            div.className = 'bi-painel-lateral-indicador bi-indicador-drop-filtros draggable holder-btn-flutuante';
            div.innerHTML = `
                <i class="bi-painel-lateral-indicador-icone fas fa-calculator fas fa-bars"></i>
                <span class="bi-painel-lateral-indicador-descricao" style="border: 1px solid #d2d2d2;border-radius: 3px;cursor: pointer;display: block;margin: 2px;padding: 2px 6px;width: 100%;">${indicador.nomeIndicador} (${indicador.descricaoTabela})</span>
                <i class="btn-flutuante indicador-btn-opcoes fas fa-bars"></i>
            `;
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

