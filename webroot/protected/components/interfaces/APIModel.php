<?php

/**
 * Model used for int he API
 * @author Han
 * 
 */
abstract class APIModel extends RelationalDocument implements APIElement {

    private function represents($context, $fields, $type) {
        $ret = array();
        foreach ($fields as $key => $val) {
            if ($val instanceof Closure || (is_array($val) && count($val) == 2)) {
                $ret[$key] = call_user_func($val, $context);
            } else if (is_array($val)) {

                $internal_val = is_object($context) ? $context->$key : $context[$key];
                $ret[$key] = $this->represents($internal_val, $val, $type);
            } else if (is_string($val) && method_exists($context, $val)) {

                $call = $context->$val();

                if ($call instanceof EMongoCursor || is_array($call)) {
                    $r = array();
                    foreach ($call as $c) {
                        $r[] = $c->getRepresentation($type);
                    }
                    $ret[$val] = $r;
                }
            } else if (isset($context->$val) && $context->$val instanceof APIModel) {
                $ret[$val] = $context->$val->getRepresentation(APIElement::REP_EMBED);
            } else {
                $ex = explode('.', $val);
                if (count($ex) > 1) {

                    $key = array_shift($ex);
                    $last_key = $key;
                    $internal_val = $context->$key;
                    while ($key = array_shift($ex)) {
                        $internal_val = is_object($internal_val) ? $internal_val->$key : $internal_val[$key];
                        $last_key = $key;
                    }

                    $ret[$last_key] = $internal_val;
                } else {
                    $internal_val = is_object($context) ? $context->$val : $context[$val];
                    $ret[$val] = $internal_val;
                }
            }
        }

        return $ret;
    }

    /**
     * Get a representation of the document using {@link fields}
     * @see APIElement::getRepresentation()
     */
    public function getRepresentation($type = APIElement::REP_FULL) {
        $fields = $this->fields($type);
        return $this->represents($this, $fields, $type);
    }

    /**
     * Fields of a certain representation of an object
     * 
     * Fields can be of 
     * 	- {string} name
     * 		results in name => $this->$name
     *  - {string} name.of.a.array.item
     *  	results in item = > $this->name['of']['a']['array']['item']
     *  - {Closure} function ($obj) { return $obj->name; }
     *  	results in key => cb($this)
     * 
     * @param string $type
     * @return array
     */
    abstract function fields($type);

    /**
     * True implementation of hasAccess
     * @see APIElement::hasAccess()
     */
    public function hasAccess($action, $context = null) {
        return true;
    }

    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * Override of RelationalDocument
     * @return array
     */
    public function getEmbed() {
        return $this->getRepresentation(APIElement::REP_EMBED);
    }

}
