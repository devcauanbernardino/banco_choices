<?php 

class Questao {
    private $arq;

    public function __construct(){
        $this->arq = __DIR__ . '/../../data/questoes_microbiologia_refinado.json';
    }

    private function carregar() {
        $json = file_get_contents($this->arq);
        return json_decode($json, true);
    }

    public function todas() {
        return $this->carregar();
    }

    public function total() {
        return count($this->carregar());
    }

    public function porId($id) {
        foreach ($this->carregar() as $questao) {
            if ($questao['id'] == $id) {
                return $questao;
            }
        }
        return null;
    }
}



?>