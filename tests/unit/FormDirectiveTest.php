<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class FormDirectiveTest extends TestCase
{
    use VerifiesOutputTrait;

    public function testRequiredDirective(): void
    {
        $this->assertSame(
            '<input type="text" name="name" required>',
            $this->renderBlade('<input type="text" name="name" @required>')
        );
    }

    public function testRequiredWithConditionals(): void
    {
        $cases = [
            [
                'condition' => 'true',
                'expected' => '<input type="text" name="name" required>',
                'message' => 'The input should be required when the condition is true',
            ],
            [
                'condition' => 'false',
                'expected' => '<input type="text" name="name" >',
                'message' => 'The input should not be required when the condition is false',
            ],
            [
                'condition' => 'null',
                'expected' => '<input type="text" name="name" >',
                'message' => 'The input should not be required when the condition is null',
            ],
            [
                'condition' => 'fn() => true',
                'expected' => '<input type="text" name="name" required>',
                'message' => 'The input should be required when the condition is a string that evaluates to true',
            ],
            [
                'condition' => '1 == "1"',
                'expected' => '<input type="text" name="name" required>',
                'message' => 'The input should be required when the condition is a string that evaluates to true',
            ]
        ];

        foreach ($cases as $case) {
            $this->assertSame(
                $case['expected'],
                $this->renderBlade(
                    '<input type="text" name="name" @required(' . $case['condition'] . ')>',
                ),
                $case['message']
            );
        }
    }

    public function testDisabledDirective(): void
    {
        $this->assertSame(
            '<input type="text" name="name" disabled>',
            $this->renderBlade('<input type="text" name="name" @disabled>')
        );
    }

    public function testDisabledDirectiveWithConditionals(): void
    {
        $cases = [
            [
                'expected' => '<input type="text" name="name" disabled>',
                'blade' => '<input type="text" name="name" @disabled(true)>',
                'message' => 'The input should be disabled when the condition is true',
            ],
            [
                'expected' => '<input type="text" name="name" >',
                'blade' => '<input type="text" name="name" @disabled(false)>',
                'message' => 'The input should not be disabled when the condition is false',
            ],
            [
                'expected' => '<input type="text" name="name" >',
                'blade' => '<input type="text" name="name" @disabled(null)>',
                'message' => 'The input should not be disabled when the condition is null',
            ],
            [
                'expected' => '<input type="text" name="name" disabled>',
                'blade' => '<input type="text" name="name" @disabled(fn() => true)>',
                'message' => 'The input should be disabled when the condition is a string that evaluates to true',
            ],
            [
                'expected' => '<input type="text" name="name" disabled>',
                'blade' => '<input type="text" name="name" @disabled(1 == "1")>',
                'message' => 'The input should be disabled when the condition is a string that evaluates to true',
            ]
        ];

        foreach ($cases as $case) {
            $this->assertSame(
                $case['expected'],
                $this->renderBlade($case['blade']),
                $case['message']
            );
        }
    }

    public function testCheckedDirective(): void
    {
        $this->assertSame(
            '<input type="checkbox" name="name" checked>',
            $this->renderBlade('<input type="checkbox" name="name" @checked>')
        );
    }

    public function testCheckedDirectiveWithConditionals(): void
    {
        $cases = [
            [
                'expected' => '<input type="checkbox" name="name" checked>',
                'blade' => '<input type="checkbox" name="name" @checked(true)>',
                'message' => 'The input should be checked when the condition is true',
            ],
            [
                'expected' => '<input type="checkbox" name="name" >',
                'blade' => '<input type="checkbox" name="name" @checked(false)>',
                'message' => 'The input should not be checked when the condition is false',
            ],
            [
                'expected' => '<input type="checkbox" name="name" >',
                'blade' => '<input type="checkbox" name="name" @checked(null)>',
                'message' => 'The input should not be checked when the condition is null',
            ],
            [
                'expected' => '<input type="checkbox" name="name" checked>',
                'blade' => '<input type="checkbox" name="name" @checked(fn() => true)>',
                'message' => 'The input should be checked when the condition is a string that evaluates to true',
            ],
            [
                'expected' => '<input type="checkbox" name="name" checked>',
                'blade' => '<input type="checkbox" name="name" @checked(1 == "1")>',
                'message' => 'The input should be checked when the condition is a string that evaluates to true',
            ]
        ];

        foreach ($cases as $case) {
            $this->assertSame(
                $case['expected'],
                $this->renderBlade($case['blade']),
                $case['message']
            );
        }
    }

    public function testSelectedDirective(): void
    {
        $this->assertSame(
            '<option value="1" selected>One</option>',
            $this->renderBlade('<option value="1" @selected>One</option>')
        );
    }

    public function testSelectedDirectiveWithConditionals(): void
    {
        $cases = [
            [
                'expected' => '<option value="1" selected>One</option>',
                'blade' => '<option value="1" @selected(true)>One</option>',
                'message' => 'The option should be selected when the condition is true',
            ],
            [
                'expected' => '<option value="1" >One</option>',
                'blade' => '<option value="1" @selected(false)>One</option>',
                'message' => 'The option should not be selected when the condition is false',
            ],
            [
                'expected' => '<option value="1" >One</option>',
                'blade' => '<option value="1" @selected(null)>One</option>',
                'message' => 'The option should not be selected when the condition is null',
            ],
            [
                'expected' => '<option value="1" selected>One</option>',
                'blade' => '<option value="1" @selected(fn() => true)>One</option>',
                'message' => 'The option should be selected when the condition is a string that evaluates to true',
            ],
            [
                'expected' => '<option value="1" selected>One</option>',
                'blade' => '<option value="1" @selected(1 == "1")>One</option>',
                'message' => 'The option should be selected when the condition is a string that evaluates to true',
            ]
        ];

        foreach ($cases as $case) {
            $this->assertSame(
                $case['expected'],
                $this->renderBlade($case['blade']),
                $case['message']
            );
        }
    }

    public function testReadOnlyDirective(): void
    {
        $this->assertSame(
            '<input type="text" name="name" readonly>',
            $this->renderBlade('<input type="text" name="name" @readonly>')
        );
    }

    public function testReadOnlyDirectiveWithConditionals(): void
    {
        $cases = [
            [
                'expected' => '<input type="text" name="name" readonly>',
                'blade' => '<input type="text" name="name" @readonly(true)>',
                'message' => 'The input should be readonly when the condition is true',
            ],
            [
                'expected' => '<input type="text" name="name" >',
                'blade' => '<input type="text" name="name" @readonly(false)>',
                'message' => 'The input should not be readonly when the condition is false',
            ],
            [
                'expected' => '<input type="text" name="name" >',
                'blade' => '<input type="text" name="name" @readonly(null)>',
                'message' => 'The input should not be readonly when the condition is null',
            ],
            [
                'expected' => '<input type="text" name="name" readonly>',
                'blade' => '<input type="text" name="name" @readonly(fn() => true)>',
                'message' => 'The input should be readonly when the condition is a string that evaluates to true',
            ],
            [
                'expected' => '<input type="text" name="name" readonly>',
                'blade' => '<input type="text" name="name" @readonly(1 == "1")>',
                'message' => 'The input should be readonly when the condition is a string that evaluates to true',
            ]
        ];

        foreach ($cases as $case) {
            $this->assertSame(
                $case['expected'],
                $this->renderBlade($case['blade']),
                $case['message']
            );
        }
    }
}
