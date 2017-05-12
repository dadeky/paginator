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
	 * Separator for IN, NOT IN
	 * @var string
	 */
	private $separator=";";
	
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
					 { oper: "bw", text: "begins with" }, // done
					 { oper: "bn", text: "not begins with " }, // done
					 { oper: "in", text: "is in" },
					 { oper: "ni", text: "is not in" },
					 { oper: "ew", text: "ends with" }, // done
					 { oper: "en", text: "not ends with" }, // done
					 */
						
					case 'cn':
						$qb->{$whereMethod}($qb->expr()->like($prefix.".".$fieldName, ':'.$fieldName));
						$qb->setParameter($fieldName, "%".$value."%");
						break;
	
					case 'nc':
						$qb->{$whereMethod}($qb->expr()->notLike($prefix.".".$fieldName, ':'.$fieldName));
						$qb->setParameter($fieldName, "%".$value."%");
						break;
	
					case 'bw':
						$qb->{$whereMethod}($qb->expr()->like($prefix.".".$fieldName, ':'.$fieldName));
						$qb->setParameter($fieldName, $value."%");
						break;
	
					case 'bn':
						$qb->{$whereMethod}($qb->expr()->notLike($prefix.".".$fieldName, ':'.$fieldName));
						$qb->setParameter($fieldName, $value."%");
						break;
	
					case 'ew':
						$qb->{$whereMethod}($qb->expr()->like($prefix.".".$fieldName, ':'.$fieldName));
						$qb->setParameter($fieldName, "%".$value);
						break;
	
					case 'en':
						$qb->{$whereMethod}($qb->expr()->notLike($prefix.".".$fieldName, ':'.$fieldName));
						$qb->setParameter($fieldName, "%".$value);
						break;
	
					case 'nu':
						$qb->{$whereMethod}($qb->expr()->isNull($prefix.".".$fieldName));
						$qb->setParameter($fieldName, $value);
						break;
	
					case 'nn':
						$qb->{$whereMethod}($qb->expr()->isNotNull($prefix.".".$fieldName));
						$qb->setParameter($fieldName, $value);
						break;
	
					case 'ge':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' >= :' . $fieldName);
						$qb->setParameter($fieldName, $value);
						break;
	
					case 'le':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' <= :' . $fieldName);
						$qb->setParameter($fieldName, $value);
						break;
	
					case 'eq':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' = :' . $fieldName);
						$qb->setParameter($fieldName, $value);
						break;
	
					case 'ne':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' != :' . $fieldName);
						$qb->setParameter($fieldName, $value);
						break;
	
					case 'lt':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' < :' . $fieldName);
						$qb->setParameter($fieldName, $value);
						break;
	
					case 'gt':
						$qb->{$whereMethod}($prefix.".".$fieldName . ' > :' . $fieldName);
						$qb->setParameter($fieldName, $value);
						break;
	
					case 'in':
						$qb->{$whereMethod}($qb->expr()->in($prefix.".".$fieldName, ':'.$fieldName));
						$qb->setParameter($fieldName, explode($this->getSeparator(),$value));
						break;
	
					case 'ni':
						$qb->{$whereMethod}($qb->expr()->notIn($prefix.".".$fieldName, ':'.$fieldName));
						$qb->setParameter($fieldName, explode($this->getSeparator(),$value));
						break;
				}
	
				/*$qb->setParameter($fieldName, $value);*/
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
	
		$this->paginatedResult = $qb->getQuery()->getResult($query->getHydrator());
	
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
	
	public function getSeparator()
	{
		return $this->separator;
	}
}