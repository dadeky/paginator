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
	
	private function resolveParameter($fieldName)
	{
	    $fieldNameArray = explode(".", $fieldName);
	    $count = count($fieldNameArray);
	    $parameter = $fieldNameArray[$count-1];
	    return $parameter;
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
				
				// in case we have a value object in the entity we want to search by eg. priority.priority 
				$parameter = $this->resolveParameter($fieldName);
				switch ($operand){
						
					case 'cn':
					    $qb->{$whereMethod}($qb->expr()->like($prefix.".".$fieldName, ':'.$parameter));
						$qb->setParameter($parameter, "%".$value."%");
						break;
	
					case 'nc':
					    $qb->{$whereMethod}($qb->expr()->notLike($prefix.".".$fieldName, ':'.$parameter));
						$qb->setParameter($parameter, "%".$value."%");
						break;
	
					case 'bw':
					    $qb->{$whereMethod}($qb->expr()->like($prefix.".".$fieldName, ':'.$parameter));
						$qb->setParameter($parameter, $value."%");
						break;
	
					case 'bn':
					    $qb->{$whereMethod}($qb->expr()->notLike($prefix.".".$fieldName, ':'.$parameter));
						$qb->setParameter($parameter, $value."%");
						break;
	
					case 'ew':
					    $qb->{$whereMethod}($qb->expr()->like($prefix.".".$fieldName, ':'.$parameter));
						$qb->setParameter($parameter, "%".$value);
						break;
	
					case 'en':
					    $qb->{$whereMethod}($qb->expr()->notLike($prefix.".".$fieldName, ':'.$parameter));
						$qb->setParameter($parameter, "%".$value);
						break;
	
					case 'nu':
					    $qb->{$whereMethod}($qb->expr()->isNull($prefix.".".$parameter));
						$qb->setParameter($parameter, $value);
						break;
	
					case 'nn':
					    $qb->{$whereMethod}($qb->expr()->isNotNull($prefix.".".$parameter));
						$qb->setParameter($parameter, $value);
						break;
	
					case 'ge':
					    $qb->{$whereMethod}($prefix.".".$fieldName . ' >= :' . $parameter);
						$qb->setParameter($parameter, $value);
						break;
	
					case 'le':
					    $qb->{$whereMethod}($prefix.".".$fieldName . ' <= :' . $parameter);
						$qb->setParameter($parameter, $value);
						break;
	
					case 'eq':
					    $qb->{$whereMethod}($prefix.".".$fieldName . ' = :' . $parameter);
						$qb->setParameter($parameter, $value);
						break;
	
					case 'ne':
					    $qb->{$whereMethod}($prefix.".".$fieldName . ' != :' . $parameter);
						$qb->setParameter($parameter, $value);
						break;
	
					case 'lt':
					    $qb->{$whereMethod}($prefix.".".$fieldName . ' < :' . $parameter);
						$qb->setParameter($parameter, $value);
						break;
	
					case 'gt':
					    $qb->{$whereMethod}($prefix.".".$fieldName . ' > :' . $parameter);
						$qb->setParameter($parameter, $value);
						break;
	
					case 'in':
					    $qb->{$whereMethod}($qb->expr()->in($prefix.".".$fieldName, ':'.$parameter));
						$qb->setParameter($parameter, explode($this->getSeparator(),$value));
						break;
	
					case 'ni':
					    $qb->{$whereMethod}($qb->expr()->notIn($prefix.".".$fieldName, ':'.$parameter));
						$qb->setParameter($parameter, explode($this->getSeparator(),$value));
						break;
				}
	
				/*$qb->setParameter($parameter, $value);*/
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
		    if (null !== $this->request->getSearchParams())
		    {
		        if (count($this->request->getSearchParams()->getSearchRules()) > 0)
		        {
		            foreach ($this->request->getSearchParams()->getSearchRules() as $rule)
		            {
		                $qb = $this->processRule($qb, $this->request->getSearchParams()->getGroupOperand(), $rule->getField(), $rule->getOperand(), $rule->getData(), $prefix);
		            }
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