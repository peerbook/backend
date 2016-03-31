<?php

class LocalBookController extends APIChildController {
    
    public function browse() {
        
        $criteria = $this->getContextClass()->getLocalCriteria($this->getContext()) + array(
            'deleted' => false
        );
                
        $filterForEan = $this->getApiRequest()->getParam('ean');
        if($filterForEan != null) {
            $criteria['ean'] = (string) $filterForEan;
        }
        
        $books = Copy::model()->find($criteria);
        
        $ret = [];
        
        $books->sort(['last_updated_date' => -1]);
        
        foreach($books as $book) {
            if(!isset($book->isAdmin))
                $ret[] = $book->getRepresentation(APIElement::REP_ITEM);
        }
        
        return $ret;
    }
    
}