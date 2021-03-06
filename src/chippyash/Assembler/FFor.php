<?php
/**
 * Lightweight assembly builder pattern
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson <ashley@zf4.biz>, 2015,2021 UK
 * @licence BSD 3 Clause see LICENSE.MD
 */
declare(strict_types=1);
namespace Assembler;

/**
 * Simple Assembler derivation
 * A Functional For Comprehension
 *
 */
class FFor extends Assembler
{
    /**
     * Assemble and return an array of variables created else just a single value
     * Usage:
     * ->fyield('var1','varN')
     * ->fyield('var1')
     *
     * @param string $var1 Name of variable to return
     * @param string $_ Next name of variable to retrieve - repeater
     *
     * @return mixed
     *
     * @throw RuntimeException
     */
    public function fyield($var1)
    {
        $this->assemble();
        return call_user_func_array([$this, 'release'], func_get_args());
    }

    /**
     * @inheritdoc
     * @throw \BadMethodCallException
     */
    public static function get(array $params = []): Assembler
    {
        throw new \BadMethodCallException('Cannot create singleton FFor Comprehension');
    }

    /**
     * @inheritdoc
     * @throw \BadMethodCallException
     */
    public function merge(Assembler $other): Assembler
    {
        throw new \BadMethodCallException('Cannot merge a FFor Comprehension');
    }
}