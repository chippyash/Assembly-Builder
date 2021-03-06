<?php
/**
 * Lightweight assembly builder pattern
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson <ashley@zf4.biz>, 2015, UK
 * @licence BSD 3 Clause see LICENSE.MD
 */
declare(strict_types=1);
namespace Assembler\Test\Assembler;

use Assembler\Assembler;
use PHPUnit\Framework\TestCase;

class AssemblerTest extends TestCase
{
    public function testYouCannotConstructAnAssemblerDirectly()
    {
        $reflection = new \ReflectionClass(Assembler::class);
        $constructor = $reflection->getConstructor();
        $this->assertFalse($constructor->isPublic());
    }

    public function testYouConstructAnAssemblerUsingTheCreateMethod()
    {
        $this->assertInstanceOf(Assembler::class, Assembler::create());
    }

    public function testYouCanReleaseMultipleValues()
    {
        [$v1, $v3] = Assembler::create()
            ->var1(function(){return 1;})
            ->var2(function($var1){return 2 + $var1;})
            ->var3(function($var1, $var2){return $var1 * 10 + $var2;})
            ->assemble()
            ->release('var1','var3');

        $this->assertEquals(1, $v1);
        $this->assertEquals(13, $v3);


    }

    public function testYouCanReleaseJustASingleValue()
    {
        $v4 = Assembler::create()
            ->var1(function(){return 1;})
            ->var2(function($var1){return 2 + $var1;})
            ->var3(function($var1, $var2){return $var1 * 10 + $var2;})
            ->assemble()
            ->release('var3');

        $this->assertEquals(13, $v4);
    }

    public function testReleasingMultipleValuesWillReturnThemInTheOrderSpecified()
    {
        [$v3, $v1, $v2] = Assembler::create()
            ->var1(function(){return 1;})
            ->var2(function($var1){return 2 + $var1;})
            ->var3(function($var1, $var2){return $var1 * 10 + $var2;})
            ->assemble()
            ->release('var3', 'var1', 'var2');

        $this->assertEquals(13, $v3);
        $this->assertEquals(3, $v2);
        $this->assertEquals(1, $v1);
    }

    public function testCreatingAVariableExpectsAClosure()
    {
        $this->expectException(\RuntimeException::class);
        Assembler::create()->var1('foo');
    }

    public function testAssemblingTheAssemblerReturnsTheAssembler()
    {
        $sut = Assembler::create()
            ->var1(function(){return 1;})
            ->var2(function($var1){return 2 + $var1;})
            ->var3(function($var1, $var2){return $var1 * 10 + $var2;})
            ->assemble();
        $this->assertInstanceOf(Assembler::class, $sut);
    }

    /**
     * @runInSeparateProcess
     */
    public function testYouCanGetASingletonInstanceOfTheAssembler()
    {
        $sut = Assembler::get()
            ->var1(function(){return 1;})
            ->var2(function($var1){return 2 + $var1;})
            ->var3(function($var1, $var2){return $var1 * 10 + $var2;})
            ->assemble();
        $this->assertInstanceOf(Assembler::class, $sut);
    }

    public function testYouCannotOverwriteAPreviouslyAssembledValue()
    {
        $sut = Assembler::create()
            ->var1(function(){return 1;})
            ->assemble();
        $v1 = $sut->release('var1');
        $this->assertEquals(1, $v1);
        $sut->var1(function(){return 2;});
        $v2 = $sut->release('var1');
        $this->assertEquals(1, $v2);
        $this->assertEquals($v1, $v2);
    }

    public function testYouCanMergeTwoAssemblies()
    {
        $sut1 = Assembler::create()
            ->var1(function(){return 1;})
            ->assemble();
        $sut2 = Assembler::create()
            ->var2(function(){return 2;})
            ->assemble();
        [$v1, $v2] = $sut1->merge($sut2)
            ->release('var1','var2');
        $this->assertEquals(1, $v1);
        $this->assertEquals(2, $v2);

        //case where other Assembly has no values
        $sut3 = Assembler::create();
        [$v1, $v2] = $sut1->merge($sut3)
            ->release('var1','var2');
        $this->assertEquals(1, $v1);
        $this->assertEquals(2, $v2);
    }

    public function testYouCanSendInParametersWhenYouCreateAnAssembler()
    {
        $sut = Assembler::create(['foo' => 'bar'])
            ->dosomething(function($foo) {return "{$foo}{$foo}";})
            ->assemble();

        [$test1, $test2] = $sut->release('dosomething', 'foo');

        $this->assertEquals('barbar', $test1);
        $this->assertEquals('bar', $test2);
    }

    /**
     * @runInSeparateProcess
     */
    public function testYouCanSendInParametersWhenYouGetAnAssembler()
    {
        $sut = Assembler::get(['foo' => 'bar'])
            ->dosomething(function($foo) {return "{$foo}{$foo}";})
            ->assemble();

        [$test1, $test2] = $sut->release('dosomething', 'foo');

        $this->assertEquals('barbar', $test1);
        $this->assertEquals('bar', $test2);
    }

    public function testParametersSentInDuringCreationAreImmutable()
    {
        $sut = Assembler::create(['foo' => 'bar'])
            ->foo(function() {return 'baz';}) //this is ignored - foo is immutable
            ->assemble();

        $test = $sut->release('foo');
        $this->assertEquals('bar', $test);
    }

    public function testFunctionParametersArePassedInTheCorrectOrder()
    {
        $sut = Assembler::create([
            'v1' => false,
            'v2' => 'foo',
            'v3' => 4
        ])
            ->foo(function($v3, $v2, $v1) {
                return $v3 . $v2 . ' ' . ($v1 ? 'true' : 'false');
            })
            ->assemble();

        $this->assertEquals('4foo false', $sut->release('foo'));
    }
}