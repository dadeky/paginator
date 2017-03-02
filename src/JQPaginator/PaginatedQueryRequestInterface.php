<?php
namespace JQPaginator;

interface PaginatedQueryRequestInterface 
{
	/**
	 * Page number to be displayed eg. 5
	 * @var integer
	 * */
	public function getPage();
	
	/**
	 * The number of items per page eg. 25
	 * @var integer
	 */
	public function getItemCount(); //eg. 25
	
	/**
	 * Is the search enabled
	 * @var boolean
	 */
	public function getSearchEnabled();
	
	/**
	 * Parameters by which the data are filtered eg. array('groupOp' => 'AND', 'rules' => array(array('field'=>'plant', 'op'=>'eq', 'data'=>500)))
	 * @var array
	 */
	public function getSearchParams();
	
	/**
	 * Order specifications eg. array('plantNr' => 'ASC', 'workCenterCode' => 'DESC')
	 * @var array
	 */
	public function getOrderSpecs();
	
}