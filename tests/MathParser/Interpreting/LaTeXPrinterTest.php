<?php

use MathParser\StdMathParser;
use MathParser\Interpreting\Interpreter;
use MathParser\Interpreting\LaTeXPrinter;
use MathParser\Interpreting\Differentiator;
use MathParser\Parsing\Nodes\Node;
use MathParser\Parsing\Nodes\FunctionNode;
use MathParser\Parsing\Nodes\VariableNode;
use MathParser\Parsing\Nodes\ExpressionNode;
use MathParser\Parsing\Nodes\NumberNode;
use MathParser\Parsing\Nodes\ConstantNode;

use MathParser\Exceptions\UnknownFunctionException;
use MathParser\Exceptions\UnknownOperatorException;
use MathParser\Exceptions\UnknownConstantException;
use MathParser\Exceptions\DivisionByZeroException;

class LaTeXPrinterTest extends PHPUnit_Framework_TestCase
{
    private $parser;
    private $printer;

    public function setUp()
    {
        $this->parser = new StdMathParser();
        $this->printer = new LaTeXPrinter();
    }

    private function assertResult($input, $output)
    {
        $node = $this->parser->parse($input);
        $result = $node->accept($this->printer);

        $this->assertEquals($result, $output);
    }

    public function testCanPrintVariable()
    {
        $this->assertResult('x', 'x');
    }

    public function testCanPrintInteger()
    {
        $this->assertResult('4', '4');
        $this->assertResult('-2', '-2');
    }

    public function testCanPrintUnaryMinus()
    {
        $this->assertResult('-x', '-x');
    }

    public function testCanAddBraces()
    {
        $node = new NumberNode('4');
        $output = $this->printer->bracesNeeded($node);

        $this->assertEquals($output, '4');

        $node = new NumberNode('-2');
        $output = $this->printer->bracesNeeded($node);

        $this->assertEquals($output, '{-2}');

        $node = new NumberNode('12');
        $output = $this->printer->bracesNeeded($node);

        $this->assertEquals($output, '{12}');

        $node = new VariableNode('x');
        $output = $this->printer->bracesNeeded($node);

        $this->assertEquals($output, 'x');

        $node = new ConstantNode('pi');
        $output = $this->printer->bracesNeeded($node);

        $this->assertEquals($output, '\pi');

        $node = $this->parser->parse('1+1');
        $output = $this->printer->bracesNeeded($node);

        $this->assertEquals($output, '{1+1}');
    }

    public function testCanPrintDivision()
    {
        $this->assertResult('1/2', '\frac{1}{2}');
    }

    public function testCanPrintMultiplication()
    {
        $this->assertResult('2*3', '2\cdot 3');
        $this->assertResult('2*x', '2x');
        $this->assertResult('2*3^2', '2\cdot 3^2');
        $this->assertResult('2*(3+4)', '2\cdot (3+4)');
        $this->assertResult('2*(x+4)', '2(x+4)');
        $this->assertResult('(x+1)*(x+2)', '(x+1)(x+2)');
    }

    public function testCanPrintFunctions()
    {
        $this->assertResult('sin(x)', '\sin x');
        $this->assertResult('cos(x)', '\cos x');
        $this->assertResult('tan(x)', '\tan x');

        $this->assertResult('exp(x)', 'e^x');
        $this->assertResult('exp(2)', 'e^2');
        $this->assertResult('exp(2x)', 'e^{2x}');
        $this->assertResult('exp(x^2)', '\exp(x^2)');

        $this->assertResult('log(x)', '\log x');
        $this->assertResult('log(2x)', '\log 2x');
        $this->assertResult('log(2+x)', '\log(2+x)');

        $this->assertResult('sqrt(x)', '\sqrt{x}');
        $this->assertResult('sqrt(x^2)', '\sqrt{x^2}');

        $this->assertResult('asin(x)', '\arcsin x');
    }

    public function testCanPrintConstant()
    {
        $this->assertResult('pi', '\pi');
        $this->assertResult('e', 'e');

        $node = new ConstantNode('xcv');
        $this->setExpectedException(UnknownConstantException::class);
        $node->accept($this->printer);
    }
}
