<?php
namespace JQPaginator;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

class Paginator {
	/**
	 * Page number to be displayed eg. 5
	 * @var integer
	 * */
	private $page;
	
	/**
	 * The number of items per page eg. 25
	 * @var integer
	 */
	private $itemCount;
	
	/**
	 * Is the search enabled
	 * @var boolean
	 */
	private $searchEnabled;
	
	/**
	 * Parameters by which the data are filtered eg. array('groupOp' => 'AND', 'rules' => array(array('field'=>'plant', 'op'=>'eq', 'data'=>500)))
	 * @var array
	 */
	private $searchParams;
	
	/**
	 * Order specifications eg. array('plantNr' => 'ASC', 'workCenterCode' => 'DESC')
	 * @var array
	 */
	private $orderSpecs;
	
	/**
	 * The total number of items in the paginator
	 * @var integer
	 */
	private $totalItems;
	
	/**
	 * The total number of pages
	 * @var integer
	 */
	private $totalPages;
	
	/**
	 * @var array
	 */
	private $paginatedResult;
	
	
	public function __construct(
		PaginatedQueryRequestInterface $params
	){
		$this->page = $params->getPage();
		$this->itemCount = $params->getItemCount();
		$this->searchEnabled = $params->getSearchEnabled();
		$this->searchParams = $params->getSearchParams();
		$this->orderSpecs = $params->getOrderSpecs();
	}
	
	private function processRule(
			QueryBuilder $qb,
			$groupOperand,
			$fieldName,
			$operand,
			$value,
			$prefix
			){
				$whereMethod = strtolower($groupOperand)."Where"; // produces andWhere or orWhere
				switch ($operand){
						
					// @todo
					/*
					 { oper: "bw", text: "poèinje sa" },
					 { oper: "bn", text: "ne poèinje sa " },
					 { oper: "in", text: "je u" },
					 { oper: "ni", text: "nije u" },
					 { oper: "ew", text: "završava sa" },
					 { oper: "en", text: "ne završava sa" },
					 */
						
					case 'cn':
						$qb->{$whereMethod}($qb->expr()->like($prefix.".".$fieldName, ':'.$fieldName));
						break;
	
					case 'nc':
						$qb->{$whereMethod}($qb->expr()->notLike($prefix.".".$fieldName, ':'.$fieldName));
						break;
	
					case 'nu':
						$qb->{$whereMethod}($qb->expr()->isNull($prefix.".".$fieldName));
						break;
	
					case 'nn':
						$qb->{$whereMethod}($qb->expr()->isNotNull($prefix.".".$fieldName));
						break;
	
					case 'ge':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' >= :' . $fieldName);
						break;
	
					case 'le':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' <= :' . $fieldName);
						break;
	
					case 'eq':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' = :' . $fieldName);
						break;
	
					case 'ne':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' != :' . $fieldName);
						break;
	
					case 'lt':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' < :' . $fieldName);
						break;
	
					case 'gt':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' > :' . $fieldName);
						break;
				}
	
				$qb->setParameter($fieldName, $value);
				return $qb;
	}
	
	private function firstResult()
	{
		return ($this->page - 1) * $this->itemCount;
	}
	
	/**
	 * Restricts the query according to the pagination properties
	 * @param QueryBuilder $qb
	 * @param string $prefix
	 * @return QueryBuilder
	 */
	public function paginate(PaginatableQueryInterface $query)
	{
		$prefix = $query->getPrefix();
		$qb = $query->getQueryBuilder();
		//search
		if($this->searchEnabled)
		{
			if (count($this->searchParams->rules) > 0)
			{
				foreach ($this->searchParams->rules as $rule)
				{
					$qb = $this->processRule($qb, $this->searchParams->groupOp, $rule->field, $rule->op, $rule->data, $prefix);
				}
			}
		}
	
		//ordering
		if (count($this->orderSpecs) > 0)
		{
			foreach ($this->orderSpecs as $field => $direction)
			{
				$qb->orderBy($prefix.".".$field,$direction);
			}
		}
	
		$this->totalItems = count($this->cloneQuery($qb->getQuery())->getScalarResult());
		$this->totalPages = ceil($this->totalItems / $this->itemCount);
	
		//pagination
		$qb->setMaxResults($this->itemCount);
		$qb->setFirstResult($this->firstResult());
	
		$this->paginatedResult = $qb->getQuery()->getResult();
	
		return $this;
	}
	
	/**
	 * Clones a query.
	 *
	 * @param Query $query The query.
	 *
	 * @return Query The cloned query.
	 */
	private function cloneQuery(Query $query)
	{
		/* @var $cloneQuery Query */
		$cloneQuery = clone $query;
	
		$cloneQuery->setParameters(clone $query->getParameters());
		$cloneQuery->setCacheable(false);
	
		foreach ($query->getHints() as $name => $value) {
			$cloneQuery->setHint($name, $value);
		}
	
		return $cloneQuery;
	}
	
	public function getPaginatedResult()
	{
		return $this->paginatedResult;
	}
	
	public function getPageNumber()
	{
		return $this->page;
	}
	
	public function getTotalPages()
	{
		return $this->totalPages;
	}
	
	public function getTotalItems()
	{
		return $this->totalItems;
	}
}