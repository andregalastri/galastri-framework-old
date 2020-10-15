<?php
/**
 * - Operators.php -
 * 
 * Trait que contém os métodos de comparação usados nos validadores da classe Validation. Cada
 * método corresponde a um operador relacional.
 */
namespace galastri\extensions\validation;

use galastri\core\Chain;

trait Operators
{
    /**
     * Métodos de comparação. Permitem comparar datas, números de valores de dados durante sua
     * validação.
     * 
     * @param mixed $delimiter         Valor que será comparado com o dado principal que está
     *                                 sendo validado.
     * 
     * @param mixed $optional          Atributo opcional cujo objetivo varia de validador para
     *                                 validador. O validador Datetime, por exemplo, utiliza este
     *                                 parâmetro para determinar o formato da data delimitadora.
     *                                 Outros validadores podem usar este parâmetro para outros
     *                                 objetivos.
     */
    public function min            ($delimiter, $optional = false) { $this->operatorChain("min",            $delimiter, ">=", $optional); return $this; }
    public function max            ($delimiter, $optional = false) { $this->operatorChain("max",            $delimiter, "<=", $optional); return $this; }
    public function smaller        ($delimiter, $optional = false) { $this->operatorChain("smaller",        $delimiter, "<" , $optional); return $this; }
    public function greater        ($delimiter, $optional = false) { $this->operatorChain("greater",        $delimiter, ">" , $optional); return $this; }
    public function diff           ($delimiter, $optional = false) { $this->operatorChain("diff",           $delimiter, "!=", $optional); return $this; }
    public function equal          ($delimiter, $optional = false) { $this->operatorChain("equal",          $delimiter, "==", $optional); return $this; }
    public function specificValues (...$delimiter)                 { $this->operatorChain("specificValues", $delimiter, "==", false);     return $this; }

    /**
     * Método que cria o elo na corrente com o operador. Todo os operadores utilizam os mesmos
     * comandos, por isso optou-se por criar um comando único que pode ser reaproveitado por
     * todos eles.
     * 
     * @param string $name             Nome do método de comparação.
     * 
     * @param mixed $delimiter         Valor que será comparado com o dado principal.
     * 
     * @param string $operator         Sinal do operador do método de comparação.
     * 
     * @param mixed $optional          Atributo opcional.
     */
    private function operatorChain($name, $delimiter, $operator, $optional)
    {
        Chain::create(
            $name,
            [
                "name"      => $name,
                "delimiter" => $delimiter,
                "operator"  => $operator,
                "optional"  => $optional,
                "attach"    => true,
            ],
            (function($chainData, $data){ return Chain::resolve($chainData, $data); })
        );
    }
}
