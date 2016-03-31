<?php

/**
 * Search and fine books by Title or Ean
 */
class SearchController extends APIChildController {
    
    public static function search($q) {
        
        return Yii::app()->cache->retrieve('search_cache_'.base64_encode($q), function () use ($q) {
            $bolSearch = Yii::app()->bol->search($q);
        
            if(count($bolSearch) > 0) {
                return $bolSearch;
            } else {
                $google_books = Yii::app()->google_books->search($q);
                
                if(count($google_books) > 0) 
                    return $google_books;
            }

            return null;
        }, false);
        
    }
    
    public function read($q) {
        $items = self::search($q);
        
        $firstBook = null;
        if($items != null) {
            foreach($items as $item) {
                if($item->type() === 'book') {
                    $firstBook = $item;
                    break;
                }
            }

            if(isset($firstBook)) {

                $rep = $firstBook->getRepresentation(APIElement::REP_ITEM);
                $rep['image'] = Yii::app()->params['cdn'].'/data/'.$rep['ean'].'_book.jpg';

                return $rep;
            }
        }
        
        throw new CHttpException(404);
    }
    
}