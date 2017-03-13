<?php
namespace Paginator\Application\DataTransformer;

use Paginator\Paginator;

interface PaginatedDataTransformerInterface 
{
	public function write(Paginator $paginator);
	public function read();
}