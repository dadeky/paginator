<?php
namespace Paginator;

class SearchGroup
{
    /** @var string */
    private $groupOperand;
    
    /** @var SearchRule[] */
    private $searchRules;
    
    public function __construct(
        $groupOperand,
        array $searchRules
    ){
        $this->setGroupOperand($groupOperand);
        $this->setSearchRules($searchRules);
    }
    /**
     * @return string $groupOperand
     */
    public function getGroupOperand()
    {
        return $this->groupOperand;
    }

    /**
     * @return SearchRule[] $searchRules
     */
    public function getSearchRules()
    {
        return $this->searchRules;
    }

    /**
     * @param string $groupOperand
     */
    public function setGroupOperand($groupOperand)
    {
        $this->groupOperand = $groupOperand;
    }

    /**
     * @param SearchRule[] $searchRules
     */
    public function setSearchRules(array $searchRules)
    {
        $this->searchRules = $searchRules;
    }

    public function addSearchRule(SearchRule $searchRule)
    {
        $this->searchRules[] = $searchRule;
    }
    
}

