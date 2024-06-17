<?php

test('Validate groupName year guessing', function ($expectedYear, $groupName) {
    $this->assertEquals($expectedYear, \App\Models\GroupName::guessGroupNameYear($groupName));
})->with([
    [1, 'fin1'],
    [2, 'cin2b'],
    [3, 'min3'],
    [4, 'mid4'],
    [1, 'msig'],
    [1, 'msig1'],
    [1, 'msig2'],
]);
