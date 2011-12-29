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
		$this->insert(array("id" => $id, "data" => $data, "ttl" => $this->date("+" . $ttl . " seconds")));
		$this->garbageCollection();
		
		return true;
	}
	
	/**
	 * Find item from the cache
	 * @param mixed $id
	 * @return mixed boolean / array
	 */
	public function get($id) {
		$row = $this->findOne(array("id" => $id, "ttl" => array('$gt' => $this->date())), array("data"));
		if ($row === null) {
			return false;
		}
		return $row;
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