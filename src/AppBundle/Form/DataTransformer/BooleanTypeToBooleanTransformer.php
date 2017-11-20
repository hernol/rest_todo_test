<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Form\Type\BooleanType;
use Symfony\Component\Form\DataTransformerInterface;

class BooleanTypeToBooleanTransformer implements DataTransformerInterface {
    
    public function transformFromString($value) {
        if ('true' === strtolower($value)) {
            return BooleanType::VALUE_TRUE;
        }
        return BooleanType::VALUE_FALSE;
    }

    public function transform($value) {
        if (true === $value || BooleanType::VALUE_TRUE === (int) $value) {
            return BooleanType::VALUE_TRUE;
        }

        return BooleanType::VALUE_FALSE;
    }

    public function reverseTransform($value) {
        if (BooleanType::VALUE_TRUE === (int) $value) {
            return true;
        }

        return false;
    }
}