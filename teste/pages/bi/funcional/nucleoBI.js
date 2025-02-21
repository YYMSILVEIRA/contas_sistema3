// Definição das tabelas, indicadores e filtros
const tabelas = [
    { id: 1, nome: "situacoes", campos: ["id","descricao"] },
    { id: 2, nome: "contas_pagar", campos: ["id","descricao","valor","data","fornecedor","id_situacao","nota_fiscal","serie_nota_fiscal","chave_da_nota_fiscal","id_categoria"] }
];

const indicadores = [
    { id: 1, nome: "valor pago", formula: "SUM(valor)" },
    { id: 2, nome: "média de gastos", formula: "AVG(valor)" },
    { id: 3, nome: "fornecedor", formula: "fornecedor" }
];

const filtros = [
    { id: 1, nome: "situacao", condicao: "id_situacao = 8" },
    { id: 2, nome: "Clientes de São Paulo", condicao: "cidade = 'São Paulo'" }
];

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
        if (!this.tabelasSelecionadas.includes(tabela)) {
            this.tabelasSelecionadas.push(tabela);
        }
    }

    // Adiciona um indicador à consulta
    adicionarIndicador(indicador) {
        if (!this.indicadoresSelecionados.includes(indicador)) {
            this.indicadoresSelecionados.push(indicador);
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
    montarConsulta() {
        if (this.tabelasSelecionadas.length === 0) {
            throw new Error("Nenhuma tabela selecionada.");
        }

        // Seleção dos indicadores
        const campos = this.indicadoresSelecionados.map(ind => ind.formula).join(", ");

        // Tabelas e JOINs
        const tabelas = this.tabelasSelecionadas.map(tab => tab.nome).join(", ");
        const joins = this.tabelasSelecionadas
            .flatMap(tab => tab.joins || [])
            .map(join => `JOIN ${join.tabela} ON ${tab.nome}.${join.campoLocal} = ${join.tabela}.${join.campoExterno}`)
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
        let consulta = `SELECT ${campos} FROM ${tabelas} ${joins} ${filtros} ${groupBy} ${orderBy}`;

        // Remove espaços em branco desnecessários
        consulta = consulta.replace(/\s+/g, " ").trim();

        return consulta;
    }
}


const nucleoBI = new NucleoBI();

//Adicionando tabelas
const tabelaContasPagar = tabelas.find(t => t.nome === "contas_pagar");
nucleoBI.adicionarTabela(tabelaContasPagar);

//Adicionando um indicador
const indicadorValorPago = indicadores.find(i => i.nome === "valor pago");
nucleoBI.adicionarIndicador(indicadorValorPago);

//Adicionar um indicador
const indicadorFornecedor = indicadores.find(i => i.nome === "fornecedor");
nucleoBI.adicionarIndicador(indicadorFornecedor)






try {
    const consulta = nucleoBI.montarConsulta();
    console.log("Consulta SQL:", consulta);
} catch (error) {
    console.error(error.message);
}