<?php
interface ExternalSource {
	/**
	 * Find a product by ean
	 * @param integer $ean
	 * @return ExternalProduct
	 */
	public function findByEan($ean);
	
	/**
	 * Find a product on param
	 * @param string $q
	 * @return array<ExternalProduct>
	 */
	public function search($q);
	
	/**
	 * Find by params
	 * @param array $params
	 * @return array<ExternalProduct>
	 */
	public function find(array $params);
}