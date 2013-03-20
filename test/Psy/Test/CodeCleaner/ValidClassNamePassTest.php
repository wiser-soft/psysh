<?php

namespace Psy\Test\CodeCleaner;

use Psy\CodeCleaner\ValidClassNamePass;

class ValidClassNamePassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->pass = new ValidClassNamePass;
    }

    /**
     * @dataProvider getInvalid
     * @expectedException \Psy\Exception\FatalErrorException
     */
    public function testProcessInvalid($code)
    {
        $stmts = $this->parse($code);
        $this->pass->process($stmts);
    }

    public function getInvalid()
    {
        // class declarations
        return array(
            // core class
            array('class StdClass {}'),
            // capitalization
            array('class stdClass {}'),

            // collisions with interfaces and traits
            array('interface stdClass {}'),
            array('trait stdClass {}'),

            // collisions inside the same code snippet
            array("
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),
            array("
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                trait Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),
            array("
                trait Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),
            array("
                trait Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                interface Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),
            array("
                interface Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                trait Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),
            array("
                interface Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),
            array("
                class Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
                interface Psy_Test_CodeCleaner_ValidClassNamePass_Alpha {}
            "),

            // namespaced collisions
            array("
                namespace Psy\\Test\\CodeCleaner {
                    class ValidClassNamePassTest {}
                }
            "),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Beta {}
                }
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Beta {}
                }
            "),

            // extends and implements
            array('class ValidClassNamePassTest extends NotAClass {}'),
            array('class ValidClassNamePassTest extends ArrayAccess {}'),
            array('class ValidClassNamePassTest implements StdClass {}'),
            array('class ValidClassNamePassTest implements ArrayAccess, StdClass {}'),
            array('interface ValidClassNamePassTest extends StdClass {}'),
            array('interface ValidClassNamePassTest extends ArrayAccess, StdClass {}'),

            // class instantiations
            array('new Psy_Test_CodeCleaner_ValidClassNamePass_Gamma();'),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    new Psy_Test_CodeCleaner_ValidClassNamePass_Delta();
                }
            "),
        );
    }

    /**
     * @dataProvider getValid
     */
    public function testProcessValid($code)
    {
        $stmts = $this->parse($code);
        $this->pass->process($stmts);
    }

    public function getValid()
    {
        return array(
            // class declarations
            array('class Psy_Test_CodeCleaner_ValidClassNamePass_Epsilon {}'),
            array('namespace Psy\Test\CodeCleaner\ValidClassNamePass; class Zeta {}'),
            array("
                namespace { class Psy_Test_CodeCleaner_ValidClassNamePass_Eta {}; }
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Psy_Test_CodeCleaner_ValidClassNamePass_Eta {}
                }
            "),
            array('namespace Psy\Test\CodeCleaner\ValidClassNamePass { class StdClass {} }'),

            // class instantiations
            array('new StdClass();'),
            array('new stdClass();'),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Theta {}
                }
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    new Theta();
                }
            "),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Iota {}
                    new Iota();
                }
            "),
            array("
                namespace Psy\\Test\\CodeCleaner\\ValidClassNamePass {
                    class Kappa {}
                }
                namespace {
                    new \\Psy\\Test\\CodeCleaner\\ValidClassNamePass\\Kappa();
                }
            "),
        );
    }
}
