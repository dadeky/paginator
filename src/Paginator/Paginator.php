<?php

namespace Paginator;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

class Paginator {
	
	/** @var AbstractPaginatedQueryRequest */
	private $request;
	
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
			AbstractPaginatedQueryRequest $request
	){
		$this->request = $request;
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
		return ($this->getPageNumber() - 1) * $this->request->getItemCount();
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
		if($this->request->getSearchEnabled())
		{
			if (count($this->request->getSearchParams()->rules) > 0)
			{
				foreach ($this->request->getSearchParams()->rules as $rule)
				{
					$qb = $this->processRule($qb, $this->request->getSearchParams()->groupOp, $rule->field, $rule->op, $rule->data, $prefix);
				}
			}
		}
	
		//ordering
		if (count($this->request->getOrderSpecs()) > 0)
		{
			foreach ($this->request->getOrderSpecs() as $field => $direction)
			{
				$qb->orderBy($prefix.".".$field,$direction);
			}
		}
	
		$this->totalItems = count($this->cloneQuery($qb->getQuery())->getScalarResult());
		$this->totalPages = ceil($this->totalItems / $this->request->getItemCount());
	
		//pagination
		$qb->setMaxResults($this->request->getItemCount());
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
		return $this->request->getPage();
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