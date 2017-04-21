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
	 * @var array
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
		$this->searchEnabled = $searchEnabled;
		$this->searchParams = $searchParams;
		$this->orderSpecs = $orderSpecs;
		$this->resultShouldBePaginated = (bool) $resultShouldBePaginated;
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
	 * 
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