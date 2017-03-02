<?php
namespace JQPaginator;

interface PaginatableQueryInterface 
{
	public function getPrefix();
	public function getQueryBuilder();
}