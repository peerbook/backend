<?php

class BolProduct extends ExternalProduct {

    private $data;

    public function source() {
        return 'bol';
    }

    public function __construct($data) {

        if ($data == null) {
            throw new CHttpException(500, 'Invalid data received');
        }

        $this->data = $data;
    }

    public function getImage() {

        // Parse Images, get biggest image available
        $imageTypes = array('XL', 'L', 'M', 'S', 'XS');
        $images = array();
        if (isset($this->data['images'])) {
            foreach ($this->data['images'] as $image) {
                if ($image['type'] == 'IMAGE') {
                    $images[$image['key']] = $image['url'];
                }
            }

            foreach ($imageTypes as $type) {
                if (isset($images[$type])) {
                    return $images[$type];
                }
            }
        }

        return null;
    }

    public function getEan() {
        return (string) $this->data['ean'];
    }

    public function type() {
        if ($this->data['gpc'] === 'book') {
            if (isset($this->data['summary']) && stripos($this->data['summary'], 'ebook') > 0) {
                return 'ebook';
            }
        }
        return $this->data['gpc'];
    }

    public function getTitle() {
        return $this->bolGetKey('title');
    }

    public function getSourceId() {
        return $this->data['id'];
    }

    public function getSub() {
        $writers = $this->bolGetEntity('Auteurs');
        $druk = $this->bolGetAttribute('DRUK');
        $year = date('Y', $this->getDate());

        $sub = '';
        if (count($writers) > 0) {
            $sub .= implode(', ', $writers);
        }
        if ($druk != null) {
            $sub .= ' - ' . trim($druk, 'e') . 'e druk';
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

    private function bolGetEntity($key) {

        if (!isset($this->data['entityGroups']))
            return array();

        $ret = array();
        foreach ($this->data['entityGroups'] as $grp) {
            if ($grp['title'] == $key) {
                if (isset($grp['entities'])) {
                    foreach ($grp['entities'] as $n) {
                        $ret[] = $n['value'];
                    }
                }
            }
        }
        return $ret;
    }

    private function bolGetAttribute($key) {

        if (!isset($this->data['attributeGroups']))
            return null;

        foreach ($this->data['attributeGroups'] as $group) {

            if (isset($group['attributes'])) {
                foreach ($group['attributes'] as $v) {
                    if ($v['key'] == $key) {
                        return $v['value'];
                    }
                }
            }
        }
        return null;
    }

    private function bolGetOther($data, $key) {
        foreach ($data as $d) {
            if ($d['key'] == $key) {
                return $d['value'];
            }
        }
        return null;
    }

    private function getDate() {

        $findDate = function ($date) {

            $maand = $jaar = $dag = 1;

            $maanden = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];

            $ex = explode(' ', $date);
            if (count($ex) == 3) {

                $dag = $ex[0];
                $maand = array_search($ex[1], $maanden);
                $jaar = $ex[2];
            } else if (count($ex) == 2) {
                $maand = array_search($ex[0], $maanden);
                $jaar = $ex[1];
            } else if (count($ex) == 1 && is_numeric($ex[0])) {
                $jaar = $ex[0];
            }

            if ($maand === false || ($jaar == 1 && $maand == 1 && $dag == 1)) {
                return null;
            }

            return mktime(12, 12, 12, $maand + 1, $dag, $jaar);
        };

        $start = $this->bolGetAttribute('RELEASEDATUM');
        
        if ($start == null && isset($this->data['summary'])) {
            $sub = explode('|', $this->data['summary']);
            foreach ($sub as $s) {
                $date_found = $findDate(trim($s));
                if ($date_found !== null) {
                    return $date_found;
                }
            }
            return 0;
        } else {
            return $findDate($start);
        }
    }

    public function getOffers() {

        if (!isset($this->data['offerData']))
            return array();

        $offers = $this->data['offerData'];
        $offers_return = array();
        if(!isset($offers['offers'])) 
            return array();
        
        foreach ($offers['offers'] as $offer) {
            $offers_return[] = array(
                'id' => $offer['id'],
                'price' => $offer['price'],
                'availability' => $offer['availabilityCode'],
                'condition' => $offer['condition'],
                'second_hand' => ($offer['seller']['id'] !== 0) // 0 is bol.com
            );
        }
        return $offers_return;
    }

    public function bolGetKey($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function getPrice() {
        $min_price = PHP_INT_MAX;

        foreach ($this->getOffers() as $offer) {
            if ($offer['price'] < $min_price) {
                $min_price = $offer['price'];
            }
        }
        return $min_price;
    }
	
	public function getCategories() {
		
		$cats = [];
		if(isset($this->data['parentCategoryPaths']) && is_array($this->data['parentCategoryPaths'])) {
			foreach($this->data['parentCategoryPaths'] as $categoryPath) {
				
				if(is_array($categoryPath['parentCategories'])) {
					foreach($categoryPath['parentCategories'] as $category) {
						$cats[] = $category['name'];
					}
				}
			}
		}
		
		return array_values(array_unique($cats));
	}

	public function getLanguage() {
		
		$conversion = [
			'Nederlands' => 'nl',
			'Engels' => 'en'
		];
		
		$taal = $this->bolGetAttribute('TAAL');
		
		if(isset($conversion[$taal]))
			return $conversion[$taal];
		
		return $taal;
	}
	
    public function getAttributes() {
        return array(
            'price' => $this->getPrice(),
            'release_date' => date('c', $this->getDate()),
            'url' => $this->bolGetOther($this->data['urls'], 'DESKTOP'),
            'authors' => $this->bolGetEntity('Auteurs'),
            'subtitle' => $this->bolGetKey('subtitle'),
            'summary' => $this->bolGetKey('summary'),
            'rating' => $this->bolGetKey('rating'),
            'language' => $this->getLanguage(),
            'edition' => $this->bolGetAttribute('DRUK'),
            'description' => $this->bolGetKey('shortDescription'),
            'publisher' => $this->bolGetEntity('Uitgever'),
            'title' => $this->getTitle(),
            'tags' => $this->getCategories(),
            'source_id' => $this->getSourceId(),
            'ean' => $this->getEan(),
            'image' => $this->getImage(),
            'type' => $this->type(),
            'source' => $this->source(),
            'offers' => $this->getOffers()
        );
	}

}
