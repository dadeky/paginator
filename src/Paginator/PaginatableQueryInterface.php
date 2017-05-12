<?php
namespace Paginator;

interface PaginatableQueryInterface 
{
	public function getPrefix();
	public function getQueryBuilder();
	public function getHydrator();
}