<?php

class CORE_View_Helper_Status
{
	public function status( $statusId )
	{
		$status = new Model_EmprestimoStatus();

		return $status->find($statusId)->nome;
	}
}