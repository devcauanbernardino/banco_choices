-- Revisão detalhada por questão (JSON) no histórico de simulados.
-- Execute manualmente se preferir, ou deixe o app criar a coluna na primeira gravação.

ALTER TABLE historico_simulados
    ADD COLUMN detalhes_json LONGTEXT NULL
    COMMENT 'JSON: detalhes por questão + tempo'
    AFTER total_questoes;
