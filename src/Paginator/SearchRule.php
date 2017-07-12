<?php
namespace Paginator;

class SearchRule
{
    private $field;
    private $operand;
    private $data;
    
    public function __construct(
        $field, $operand, $data
    ){
        $this->setField($field);
        $this->setOperand($operand);
        $this->setData($data);
    }
    
    /**
     * @return string $field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string $operand
     */
    public function getOperand()
    {
        return $this->operand;
    }

    /**
     * @return string $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @param string $operand
     */
    public function setOperand($operand)
    {
        $this->operand = $operand;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

}

