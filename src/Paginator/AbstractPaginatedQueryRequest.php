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
	
	/**
	 * Always order by this spec
	 * @var \stdClass
	 */
	protected $mandatoryOrderSpecs;
	
	public function __construct(
			$page,
			$itemCount,
			$searchEnabled,
			$searchParams,
			$orderSpecs,
			$resultShouldBePaginated,
	        \stdClass $mandatoryOrderSpecs = null
	){
		$this->page = $page;
		$this->itemCount = $itemCount;
		$this->setSearchEnabled($searchEnabled);
		$this->setResultShouldBePaginated($resultShouldBePaginated);
		$this->orderSpecs = $orderSpecs;
		$this->setSearchParams($searchParams);
		$this->setMandatoryOrderSpecs($mandatoryOrderSpecs);
	}

    public function getMandatoryOrderSpecs()
    {
        return $this->mandatoryOrderSpecs;
    }

    public function setMandatoryOrderSpecs($mandatoryOrderSpecs)
    {
        $this->mandatoryOrderSpecs = $mandatoryOrderSpecs;
    }

    /**
     * @param $resultShouldBePaginated
     */
    public function setResultShouldBePaginated($resultShouldBePaginated)
    {
        if ($resultShouldBePaginated == "true"){
            $resultShouldBePaginated = true;
        }elseif ($resultShouldBePaginated == "false"){
            $resultShouldBePaginated = false;
        }else{
            $resultShouldBePaginated = boolval($resultShouldBePaginated);
        }
        $this->resultShouldBePaginated = $resultShouldBePaginated;
    }

    /**
     * @param $searchEnabled
     */
    public function setSearchEnabled($searchEnabled)
    {
        if ($searchEnabled == "true"){
            $searchEnabled = true;
        }elseif ($searchEnabled == "false"){
            $searchEnabled = false;
        }else{
            $searchEnabled = boolval($searchEnabled);
        }
        $this->searchEnabled = $searchEnabled;
    }

    /**
	 * 
	 * @param SearchGroup[] | \stdClass $searchParams
	 */
	public function setSearchParams($searchParams)
	{
        if ($searchParams instanceof SearchGroup){
            $this->searchParams = $searchParams;
        }else{
            if (isset($searchParams->rules) && count($searchParams->rules) > 0)
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