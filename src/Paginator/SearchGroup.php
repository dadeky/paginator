<?php
namespace Paginator;

class SearchGroup
{
    /** @var string */
    private $groupOperand;
    
    /** @var SearchRule[] */
    private $searchRules;
    
    /** @var SearchGroup[] */
    private $groups;
    
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
     * @return SearchGroup[] $searchGroups
     */
    public function getGroups()
    {
        return $this->groups;
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
    
    public function addGroup(SearchGroup $group)
    {
        $this->groups[] = $group;
        return $this;
    }
}

