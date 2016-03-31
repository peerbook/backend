<?php

class GoogleBook extends ExternalProduct {
    
    private $item;
    
    public function __construct($item) {
        $this->item = $item;
    }
    
    public function getAttributes() {
        return [];
    }

    public function getEan() {

        $idens = isset($this->item['volumeInfo']['industryIdentifiers']) ? $this->item['volumeInfo']['industryIdentifiers'] : [];
        
        foreach($idens as $iden) {
            if($iden['type'] == 'ISBN_13')
                return $iden['identifier'];
        }
        
        return null;
    }

    public function getImage() {
        return isset($this->item['volumeInfo']['imageLinks']['thumbnail']) ? $this->item['volumeInfo']['imageLinks']['thumbnail'] : null;
    }

    private function getAuthors() {
        return isset($this->item['volumeInfo']['authors']) ? $this->item['volumeInfo']['authors'] : [];
    }
    
    public function getSub() {
        $writers = $this->getAuthors('Auteurs');
        $year = $this->item['volumeInfo']['publishedDate'];

        $sub = '';
        if (count($writers) > 0) {
            $sub .= implode(', ', $writers);
        }
        if ($year > 1) {
            $sub .= ' - ' . $year;
        }

        return $sub;
    }

    public function getRepresentation($type = APIElement::REP_FULL) {
        return array(
            'source' => $this->source(),
            'sourceId' => $this->getSourceId(),
            'ean' => $this->getEan(),
            'title' => $this->getTitle(),
            'sub' => $this->getSub(),
            'image' => $this->getImage()
        );
    }

    public function getSourceId() {
        return $this->item['id'];
    }

    public function getTitle() {
        return isset($this->item['volumeInfo']['title']) ? $this->item['volumeInfo']['title'] : null;
    }

    public function source() {
        return 'google_books';
    }

    public function type() {
        return isset($this->item['volumeInfo']['printType']) ? strtolower($this->item['volumeInfo']['printType']) : null;
    }

}