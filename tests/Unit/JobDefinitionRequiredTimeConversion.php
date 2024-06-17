<?php

use App\Enums\RequiredTimeUnit as RTU;

test('4 periods equal 3 hours', function () {
    expect(RTU::Convert(4, RTU::PERIOD, RTU::HOUR))
        ->toEqual(3);
});

test('3 hours give 4 periods', function () {
    expect(RTU::Convert(3, RTU::HOUR, RTU::PERIOD))
        ->toEqual(4);
});
