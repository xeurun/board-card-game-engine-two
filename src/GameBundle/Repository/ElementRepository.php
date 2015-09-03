<?php

namespace GameBundle\Repository;

use GameBundle\Entity\Element;

/**
 * ElementRepository
 */
class ElementRepository extends BaseRepository
{
    /**
     * @param string $code
     * @param string|null $type
     * @param string|null $caption
     * @return Element
     */
    public function findOrCreate($code, $type = null, $caption = null)
    {
        $element = $this->findOneBy(['code' => $code]);
        if(!$element instanceof Element)
        {
            $element = new Element($code);
            $element->setType($type);
            $element->setCaption($caption);
            $this->save($element);
        }

        return $element;
    }
}