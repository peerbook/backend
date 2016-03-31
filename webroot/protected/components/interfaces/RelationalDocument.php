<?php

/**
 * Document that support embedding and relational support
 */
class RelationalDocument extends EMongoDocument {

    /**
     * Supports embedding or not?
     * @var boolean
     */
    private $_embed = true;
    private $_embed_update_references = false;
    private $_force_update_embedded = false;

    /**
     * Fully load a partial model
     */
    public function fullReload() {
	$this->setIsPartial(false);
	$this->setProjectedFields(array());
	$this->refresh();

	return $this;
    }

    /**
     * Enable or Disbale embedding
     * @param boolean $value
     */
    public function setIsEmbed($value) {
	$this->_embed = $value;
    }

    /**
     * Is embedding used in this model
     * @return boolean
     */
    public function getIsEmbed() {
	return $this->_embed;
    }

    /**
     * Force embedded values to be updated
     * @param type $value
     */
    public function setForceEmbedded($value) {
	$this->_force_update_embedded = $value;
    }

    /**
     * validator helper to help mongodbref instances
     * @param string $field 
     */
    public function isReference($field) {
	if ($this->isNewRecord && $this->_embed) {
	    $model = $this->$field;
	    if (!($model instanceof RelationalDocument) || !isset($model->_id)) {
		$this->addError($field, 'Field "' . $field . '" must be instanceof EMongoDocument');
	    } else {
		$newField = $field . '_ref';
		$this->$newField = MongoDbRef::create($model->collectionName(), $model->_id);
	    }
	}
    }

    /**
     * Get related to support retrieving by the normal relational way, but more efficient because of the use of embedded values
     * @param string $name
     * @param boolean $refresh
     * @param mixed $params
     * @return mixed
     */
    public function getRelated($name, $refresh = false, $params = array()) {

	$relations = $this->relations();
	$relation = $relations[$name];

	// must be one and with on
	if ($this->_embed && $relation[0] === 'one' && isset($relation['on'], $relation['embed']) && $relation['embed'] && !$this->isNewRecord) {
	    $cname = $relation[1];
	    $newObject = $cname::model($cname);
	    $embedObjectName = '_embed_' . $name;

	    // Does the embed object exists?
	    if (isset($this->{$relation['on']}['$id'])) {

		$_id = $this->{$relation['on']}['$id'];

		// get data and append the _id for directly usage of the object
		$embedData = [];
		if (isset($this->$embedObjectName))
		    $embedData = $this->$embedObjectName;

		$embedData['_id'] = $_id;

		$record = $newObject->populateRecord($embedData, true, true);

		$this->__set($name, $record);
		return $record;
	    }
	}

	// otherwise
	return parent::getRelated($name, $refresh, $params);
    }

    /**
     * After Validate run the embed sequence
     * @return boolean
     */
    public function afterValidate() {

	// Is new and embedding
	if ($this->_force_update_embedded || ($this->isNewRecord && $this->_embed)) {
	    foreach ($this->relations() as $relation => $options) {
		// is embedding? update record
		if (isset($options['embed']) && $options['embed']) {
		    $embedKey = '_embed_' . $relation;
		    $relationObject = $this->$relation;
		    if (isset($relationObject) && $relationObject instanceof RelationalDocument) {

			// must run on full objects in order to properly embed the values
			if ($relationObject->isPartial)
			    $relationObject->fullReload();

			$embedData = $this->$relation->getEmbed();
			if (count($embedData) > 0)
			    $this->$embedKey = $embedData;
		    }
		}
	    }

	    // store hash of embedded fields
	    if (count($this->getEmbed()) > 0)
		$this->_embed_hash = $this->embedHash();
	}

	// calculate new hash on new update, changed? than update the other products as well
	$newEmbedHash = $this->embedHash();
	if (!$this->isNewRecord && $this->_embed_hash !== $newEmbedHash) {
	    $this->_embed_update_references = true;
	    $this->_embed_hash = $newEmbedHash;
	}

	return parent::beforeValidate();
    }

    /**
     * Calculate embed hash of the embedded values
     * @return string
     */
    public function embedHash() {
	return md5(serialize($this->getEmbed()));
    }

    /**
     * After save with a existing document and hash is changed, update all children
     * Do this after the original save because that one is more important
     */
    public function afterSave() {

	parent::afterSave();

	// Embedding and changed?
	if ($this->_embed && $this->_embed_update_references) {
	    foreach ($this->relations() as $options) {

		if (isset($options['embedded'])) {
		    $cname = $options[1];
		    $name = isset($options['embedded_relation']) ? $options['embedded_relation'] : strtolower(get_class($this));

		    $model = $cname::model($cname);
		    // Update al children corresponding the embedded values
		    $model->updateAll(array(
			$options[2] => $this->_id
		    ), array(
			'$set' => array(
			    '_embed_' . $name => $this->getEmbed()
			)
		    ));
		}
	    }
	}
    }

    /**
     * Get embedding representation
     * @return array
     */
    public function getEmbed() {
	$ret = array();
	foreach ($this->embeddedFields() as $field) {
	    $ret[$field] = $this->$field;
	}
	return $ret;
    }

    /**
     * Get the fields that are included in the embedding process
     * @return array
     */
    public function embeddedFields() {
	return array();
    }

}
