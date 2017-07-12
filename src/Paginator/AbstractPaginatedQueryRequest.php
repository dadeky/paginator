<?php
namespace Paginator;

abstract class AbstractPaginatedQueryRequest 
{
	/**
	 * Page number to be displayed eg. 5
	 * @var integer
	 * */
	protected $page;
	
	/**
	 * The number of items per page eg. 25
	 * @var integer
	 */
	protected $itemCount;
	
	/**
	 * Is the search enabled
	 * @var boolean
	 */
	protected $searchEnabled;
	
	/**
	 * Parameters by which the data are filtered eg. array('groupOp' => 'AND', 'rules' => array('field'=>'plant', 'op'=>'eq', 'data'=>500))
	 * @var SearchGroup[]
	 */
	protected $searchParams;
	
	/**
	 * Order specifications eg. array(0 => 'plantNr ASC', 1 => 'workCenterCode DESC')
	 * @var array
	 */
	protected $orderSpecs;
	
	/** @var bool */
	protected $resultShouldBePaginated = true;
	
	public function __construct(
			$page,
			$itemCount,
			$searchEnabled,
			$searchParams,
			$orderSpecs,
			$resultShouldBePaginated
	){
		$this->page = $page;
		$this->itemCount = $itemCount;
		$this->searchEnabled = (bool) $searchEnabled;
		$this->orderSpecs = $orderSpecs;
		$this->resultShouldBePaginated = (bool) $resultShouldBePaginated;
		
		$this->setSearchParams($searchParams);
	}
	
	/**
	 * 
	 * @param SearchGroup[] | \stdClass $searchParams
	 */
	public function setSearchParams($searchParams)
	{
        if ($searchParams instanceof SearchGroup){
            $this->searchParams = $searchGroup;
        }else{
            if (count($searchParams->rules) > 0)
            {
                $rules = [];
                foreach ($searchParams->rules as $rule){
                    $rules[] = new SearchRule($rule->field, $rule->op, $rule->data);
                }
                $this->searchParams = new SearchGroup($searchParams->groupOp, $rules);
            }
        }
	}
	
	/**
	 * @return number
	 */
	public function getPage() {
		return $this->page;
	}
	
	/**
	 * @return number
	 */
	public function getItemCount() {
		return $this->itemCount;
	}
	
	/**
	 * @return boolean
	 */
	public function getSearchEnabled() {
		return $this->searchEnabled;
	}
	
	/**
	 * @return SearchGroup[]
	 */
	public function getSearchParams() {
		return $this->searchParams;
	}
	
	/**
	 * 
	 */
	public function getOrderSpecs() {
		return $this->orderSpecs;
	}
	
	/**
	 * @return boolean
	 */
	public function getResultShouldBePaginated() {
		return $this->resultShouldBePaginated;
	}
}