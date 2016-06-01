<?php
/**
  * Price to String transformer
  * Price is stored as an integer (Value x 100)
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transform a price value (Integer with value x 100) to a string (With € sign)
 */
class PriceTransformer implements DataTransformerInterface {
    /**
     * Transform a value (Integer) to a string
     */
    public function transform($value) {
        return sprintf("%.2f €", $value / 100.0);
    }

    /** 
     * Transform a price string to an integer (value x 100)
     */
    public function reverseTransform($str) {
        $str = str_replace(",", ".", $str);
        return floatval($str) * 100;
    }

}

