// Função para configurar o menu de contexto
function setupContextMenu(card, indicadorDescricao) {
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
                toastr.warning(`Indicador "${indicadorDescricao}" removido do painel de análise.`);
            }
            contextMenu.style.display = "none"; // Fecha o menu após a ação
        });
    });
}

$(document).ready(function () {
    // Configuração do Toastr para notificações
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
    };
    toastr.success("Bem-vindo ao Sistema de BI!");

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
                
                const Tab = descricaoElement.closest('.bi-painel-lateral-indicador').getAttribute('data-tab');
                const col = descricaoElement.closest('.bi-painel-lateral-indicador').getAttribute('data-col');
                const tabCol = descricaoElement.closest('.bi-painel-lateral-indicador').getAttribute('data-tab-col');
                
                toastr.info(`Indicador "${indicadorDescricao}" adicionado ao painel de análise.`);
                
                // Cria um novo card para o indicador
                const newCard = document.createElement("div");
                newCard.className = "bi-grid draggable-card"; // Adiciona a classe draggable-card
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
                setupContextMenu(newCard, indicadorDescricao);
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
        //console.log("Dados retornados pela API:", Indicadores); // Verifique os dados

        const divContainer = document.querySelector('.bi-painel-lateral-conteudo');
        divContainer.innerHTML = ""; // Limpa o conteúdo existente

        // Adiciona cada indicador ao painel lateral
        Indicadores.forEach(indicador => {
            const div = document.createElement('div');
            
            //Definindo os campos da tabela
            div.setAttribute('data-tab', indicador.nomeTabela);
            div.setAttribute('data-col',indicador.nomeIndicador);
            div.setAttribute('data-tab-col', indicador.nomeTabela+"."+indicador.nomeIndicador);

            div.title = indicador.descricaoIndicador;
            div.className = 'bi-painel-lateral-indicador bi-indicador-drop-filtros draggable holder-btn-flutuante';
            div.innerHTML = `
                <i class="bi-painel-lateral-indicador-icone fas fa-calculator fas fa-bars"></i>
                <span class="bi-painel-lateral-indicador-descricao" style="border: 1px solid #d2d2d2;border-radius: 3px;cursor: pointer;display: block;margin: 2px;padding: 2px 6px;width: 100%;" >${indicador.nomeIndicador}(${indicador.descricaoTabela})</span>
                <i class="btn-flutuante indicador-btn-opcoes fas fa-bars"></i>
            `;
            divContainer.appendChild(div);

            // Configura o menu de contexto para o novo indicador
            setupContextMenu(div, indicador.nomeIndicador);
        });
    } catch (erro) {
        console.error('Erro:', erro);
    }
}
