<?php

class ApiPagination extends CPagination {

    private $_currentPage;

    public function getCurrentPage($recalculate = true) {
	return $this->_currentPage ? $this->_currentPage : 0;
    }

    public function setCurrentPage($page) {
	$this->_currentPage = $page;
    }

}
