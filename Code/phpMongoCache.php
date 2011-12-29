<?php

/**
 * Simple MongoDB (and MongoHelper <3) based cache for PHP
 * Please see https://github.com/JuhQ/phpMongoCache and https://github.com/JuhQ/MongoHelper
 * @author Juha Tauriainen juha@bin.fi @juha_tauriainen
 */
class phpMongoCache extends MongoHelper {
	
	/**
	 * Set data to cache
	 * @param mixed $id
	 * @param mixed $data
	 * @param int $ttl seconds
	 * @return mixed array / boolean
	 */
	public function set($id, $data, $ttl = 1) {
		return $this->insert(array("id" => $id, "data" => $data, "ttl" => $this->date("+" . $ttl . " seconds")));
	}
	
	/**
	 * Replace item from the cache
	 * @param mixed $id
	 * @param mixed $data
	 * @param int $ttl seconds
	 * @return boolean
	 */
	public function replace($id, $data, $ttl = 1) {
		if ($this->get($id)) {
			$this->update(array("id" => $id, array('$set' => array("data" => $data, "ttl" => $this->date("+" . $ttl . " seconds")))));
		} else {
			$this->set($id, $data, $ttl);
		}
		
		return true;
	}
	
	/**
	 * Find items from the cache
	 * @param mixed $id
	 * @param array $fields
	 * @return mixed boolean / array
	 */
	public function get($id, $fields = array()) {
		$rows = $this->find(array("id" => $id), $fields);
		if ($rows->count() == 0) {
			return false;
		}
		return $rows;
	}
	
	/**
	 * Find one item from the cache
	 * @param mixed $id
	 * @param array $fields
	 * @return mixed boolean / array
	 */
	public function getOne($id, $fields = array()) {
		$row = $this->findOne(array("id" => $id), $fields);
		if ($row === null) {
			return false;
		}
		
		return $row;
	}
	
	/**
	 * Delete items from cache based on cache identifier
	 * @param mixed $id
	 * @return boolean
	 */
	public function delete($id) {
		return $this->remove(array("id" => $id));
	}
	
	/**
	 * Remove expired items, this should be running on a cronjob
	 * @return boolean
	 */
	public function garbageCollection() {
		return $this->remove(array("ttl" => array('$lt' => $this->date())));
	}
}