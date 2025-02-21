<?php

class GerenciadorPermissoes {
    private $BaseDados;

    // Construtor: recebe a instância do banco de dados
    public function __construct($BaseDados) {
        $this->BaseDados = $BaseDados;
    }

    // Método para verificar permissão para plataforma
    public function plataforma($id_perfil, $nome_plataforma) {
        $sql = "SELECT COUNT(*) AS permitido 
                FROM perfil p
                JOIN perfil_permissoes_plataformas ppp ON (p.id = ppp.id_perfil AND p.situacao = ppp.situacao)
                JOIN plataformas ptfm ON (ppp.id_plataforma = ptfm.id)
                WHERE p.id = ? 
                AND UPPER(ptfm.descricao) = UPPER(?)
                AND p.situacao = 'ATIVO'
                AND ppp.acao = 'PERMITIR'";

        $resultado = $this->BaseDados->querySingle($sql, [$id_perfil, $nome_plataforma]);
        return $resultado['permitido'] > 0;
    }

    // Método para verificar permissão para página
    public function pagina($id_perfil, $nome_plataforma, $nome_pagina) {
        $sql = "SELECT
                    COUNT(*) AS permitido 
                FROM 
                    perfil p
                    JOIN perfil_permissoes_pagina ppp ON (p.id=ppp.id_perfil AND p.situacao=ppp.situacao)
                    join plataformas ptfm on (ppp.id_plataforma=ptfm.id and ppp.situacao=ptfm.situacao)
                    JOIN paginas pgn ON (ppp.id_pagina=pgn.id)
                WHERE
                    p.id = ? 
                    AND UPPER(ptfm.descricao) = UPPER(?)
                    AND UPPER(pgn.descricao) = upper(?)
                    AND p.situacao = 'ATIVO'
                    AND ppp.acao = 'PERMITIR'";

        $resultado = $this->BaseDados->querySingle($sql, [$id_perfil, $nome_plataforma, $nome_pagina]);
        return $resultado['permitido'] > 0;
    }

    // Método para verificar permissão para campo
    public function campo($id_perfil, $nome_plataforma, $nome_pagina, $nome_campo) {
        $sql = "SELECT
                    COUNT(*) AS permitido
                FROM
                    perfil p
                JOIN perfil_permissoes_campos ppc ON
                    (p.id = ppc.id_perfil
                        AND p.situacao = ppc.situacao)
                JOIN plataformas ptfm ON
                    (ppc.id_plataforma = ptfm.id
                        AND ppc.situacao = ptfm.situacao)
                JOIN paginas pgn ON
                    (ppc.id_pagina = pgn.id)
                JOIN campos c ON
                    (ppc.id_campo=c.id)
                WHERE
                    0=0
                    AND p.id = ?
                    AND UPPER(ptfm.descricao) = UPPER(?)
                    AND UPPER(pgn.descricao) = upper(?)
                    AND UPPER(c.descricao) = upper(?)
                    AND UPPER(p.situacao) = 'ATIVO'
                    AND UPPER(ppc.acao) = 'PERMITIR'";

        $resultado = $this->BaseDados->querySingle($sql, [$id_perfil, $nome_plataforma, $nome_pagina, $nome_campo]);
        return $resultado['permitido'] > 0;
    }
}

?>
